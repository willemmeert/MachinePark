<?php
/* Copyright (C) 2021 Willem Meert <willem.meert@mema.be>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    machinepark/lib/machinepark.lib.php
 * \ingroup machinepark
 * \brief   Library files with common functions for MachinePark
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function machineparkAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("machinepark@machinepark");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/machinepark/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/machinepark/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/machinepark/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@machinepark:/machinepark/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@machinepark:/machinepark/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'machinepark@machinepark');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'machinepark@machinepark', 'remove');

	return $head;
}

/**
 * 		Show html area for list of machines
 *
 *		@param	Conf		$conf			Object conf
 * 		@param	Translate	$langs			Object langs
 * 		@param	DoliDB		$db				Database handler
 * 		@param	Object		$object			Third party object
 *      @param  string		$backtopage		Url to go once contact is created
 *      @param  int         $nocreatelink   1=Hide create project link
 *      @param	string		$newcardbutton	link to create new machine
 *      @return	int
 */
function show_machines($conf, $langs, $db, $object, $backtopage = '', $nocreatelink = 0, $newcardbutton = '')
{
	$i = -1;
	
	print "\n";
	print load_fiche_titre($langs->trans("MachinesAtThisThirdParty"), $newcardbutton, 'industry');

	print '<div class="div-table-responsive">';
	print "\n".'<table class="noborder" width=100%>';

	$sql  = "SELECT m.rowid as id, m.date_buy as date_buy, m.serial as serialno, ";
	$sql .= " p.ref as preference, p.description as pdescription, f.nom as supplier";
	$sql .= " FROM ".MAIN_DB_PREFIX."machinepark_productpark as m";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product as p on m.fk_prod = p.rowid";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as f on m.fk_fourn = f.rowid";
	$sql .= " WHERE m.fk_soc = ".$object->id;
//	$sql .= " AND p.entity IN (".getEntity('project').")";
	$sql .= " ORDER BY m.date_buy DESC";

	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);

		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("MachineparkProductCode").'</td>';
		print '<td>'.$langs->trans("MachineparkDescription").'</td>';
		print '<td class="center">'.$langs->trans("MachineparkDateBuy").'</td>';
		print '<td>'.$langs->trans("MachineparkSerial").'</td>';
		print '<td>'.$langs->trans("MachineparkSupplier").'</td>';
		print '</tr>';
		
		if ($num > 0) {
			require_once DOL_DOCUMENT_ROOT.'/custom/machinepark/class/productpark.class.php';
			
			$i = 0;
			$productstatic = new ProductPark($db);
			
			while ($i < $num) {
				$obj = $db->fetch_object($result);
				$productstatic->rowid = $obj->id;
				$productstatic->date_buy = $obj->date_buy;
				$productstatic->serial = $obj->serialno;
				$productreference = $obj->preference;
				$productdescription = $obj->pdescription;
				$productsupplier = $obj->supplier;
				
				print '<tr class="oddeven">';
				
				// Product code
				print '<td>';
				print $productreference;
				print '</td>';
				
				// Product description
				print '<td>';
				print $productdescription;
				print '</td>';
				
				// Date bought, without time
				print '<td>';
				print dol_print_date($productstatic->date_buy,'day');
				print '</td>';
				
				// Product serial number
				print '<td>';
				print $productstatic->serial;
				print '</td>';
				
				// Supplier name
				print '<td>';
				print $productsupplier;
				print '</td></tr>'."\n";	
				$i++;
			}
		}
		else {
			print '<tr><td span=5>'.$langs->trans('MachineParkNoProducts').'</td></tr>'."\n";
		}
	}
	print '</table></div>';
	
	return $i;
}
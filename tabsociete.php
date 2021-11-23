<?php
/* Copyright (C) 2021  Willem Meert         <willem.meert@mema.be>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/custom/machinepark/tabsociete.php
 *  \ingroup    machinepark
 *  \brief      Tab Machinepark in Third party pages
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once './lib/machinepark.lib.php';

$langs->loadLangs(array("companies", "commercial", "machinepark"));

// Security check
$socid = GETPOST('socid', 'int');
if ($user->socid) {
	$socid = $user->socid;
}
$result = restrictedArea($user, 'societe', $socid, '&societe');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('machineparkthirdparty'));


$action		= (GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view');
$cancel		= GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$confirm	= GETPOST('confirm', 'alpha');

/*
 *	Actions
 */

$parameters = array('id'=>$socid);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}


/*
 *	View
 */
$object = new Societe($db);
if ($socid > 0) {
	$object->fetch($socid);
}

$title = $langs->trans("Machinepark");
if (!empty($conf->global->MAIN_HTML_TITLE) && preg_match('/thirdpartynameonly/', $conf->global->MAIN_HTML_TITLE) && $object->name) {
	$title = $object->name." - ".$title;
}
llxHeader('', $title);

$head = societe_prepare_head($object);

print dol_get_fiche_head($head, 'machinepark', $langs->trans("ThirdParty"), -1, 'company');

$linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

dol_banner_tab($object, 'socid', $linkback, ($user->socid ? 0 : 1), 'rowid', 'nom');

print '<div class="fichecenter">';

print '<div class="underbanner clearboth"></div>';
print '<table class="border centpercent tableforfield">';

// Type Prospect/Customer/Supplier
print '<tr><td class="titlefield">'.$langs->trans('NatureOfThirdParty').'</td><td>';
print $object->getTypeUrl(1);
print '</td></tr>';

if ($object->client) {
	$langs->load("compta");

	print '<tr><td>';
	print $langs->trans('CustomerCode').'</td><td>';
	print showValueWithClipboardCPButton(dol_escape_htmltag($object->code_client));
	$tmpcheck = $object->check_codeclient();
	if ($tmpcheck != 0 && $tmpcheck != -5) {
		print ' <font class="error">('.$langs->trans("WrongCustomerCode").')</font>';
	}
	print '</td></tr>';

	print '<tr>';
	print '<td>';
	print $form->editfieldkey("CustomerAccountancyCode", 'customeraccountancycode', $object->code_compta, $object, $user->rights->societe->creer);
	print '</td><td>';
	print $form->editfieldval("CustomerAccountancyCode", 'customeraccountancycode', $object->code_compta, $object, $user->rights->societe->creer);
	print '</td>';
	print '</tr>';
}

print '</table>';

print '</div>';

print dol_get_fiche_end();
print '<br>';

// Machine list

$backtopage = $_SERVER['PHP_SELF'].'?socid='.$object->id;
$newcardbutton = dolGetButtonTitle($langs->trans("NewMachine"), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/custom/machinepark/productpark_card.php?action=create&socid='.$object->id.'&backtopageforcancel='.urlencode($backtopage), '', 1, $params);

$result = show_machines($conf, $langs, $db, $object, $backtopage, 1,  $newcardbutton);

llxFooter();


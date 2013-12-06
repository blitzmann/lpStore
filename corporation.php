<?php

require_once 'config.php'; 

# @todo: filter
$corpID = $_GET['corporation'];

$tpl->corpName = Db::qColumn("SELECT `itemName` FROM `invUniqueNames` WHERE `itemID` = ?", array($corpID));
$tpl->lpStore = new LpStore($corpID);    

$tpl->filterGroups = array(
  2 => 'Blueprints',
  4 => 'Ships',
  9 => 'Modules',
  11 => 'Charges',
  19 => 'Charters',
  24 => 'Implants/Boosters',
  150 => 'Skills',
  157 => 'Drones',
  475 => 'Datacores',
//  477 => 'Starbase & Sovereignty Structures',
//  955 => 'Ship Modifications',
//  1320 => 'Planetary Infrastructure',
  1396 => 'Apparel',
//  1659 => 'Special Edition Assets',
//  350001 => 'Infantry Gear'
);

asort($tpl->filterGroups);

$tpl->display('corporation.html');
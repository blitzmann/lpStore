<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$corpID   = $_GET['corporation'];
$corpName = Db::qColumn("SELECT `itemName` FROM `invUniqueNames` WHERE `itemID` = ?", array($corpID));

$lpStore = new LpStore($corpID);    



$TBS->LoadTemplate('corporation.html');
$TBS->MergeBlock('offers_blk', $lpStore->offers);
$TBS->Show();
?>
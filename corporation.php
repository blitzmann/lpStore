<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$corpID   = $_GET['corporation'];
$corpName = $DB->q1("SELECT `itemName` FROM `invUniqueNames` WHERE `itemID` = ?", array($corpID));

$offers = $DB->qa('
            SELECT a . * , b.typeName, c.*
            FROM lpStore a
            NATURAL JOIN lpOffers c
            INNER JOIN invTypes b ON ( c.typeID = b.typeID ) 
            WHERE a.corporationID = ?
            ORDER BY c.`lpCost` , c.iskCost, b.typeName', array($corpID));      

$TBS->LoadTemplate('corporation.html');
$TBS->MergeBlock('offers_blk', $offers);
$TBS->Show();
?>
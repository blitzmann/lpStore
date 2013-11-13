<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$offerID = $_GET['offerID'];
$offerName = Db::qColumn("
    SELECT `invTypes`.`typeName` 
    FROM `lpOffers`
    NATURAL JOIN `invTypes` WHERE `lpOffers`.`offerID` = ?", array($offerID));

$TBS->LoadTemplate('offer.html');
$TBS->Show();
?>
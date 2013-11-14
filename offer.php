<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$offerID = $_GET['offerID'];
$offer = new LpOffer($offerID);
$offer->calc('sell');

$similar = Db::q('SELECT * FROM `lpOffers` NATURAL JOIN `lpStore` WHERE `typeID` = :typeID GROUP BY `offerID`', array(':typeID'=>$offer->offerDetails['typeID']));
$stores  = Db::q('SELECT s.*, u.`itemName` FROM `lpStore` s INNER JOIN `invUniqueNames` u ON (u.`itemID` = s.`corporationID`) WHERE `offerID` = :offerID', array(':offerID'=>$offer->offerID));

$TBS->LoadTemplate('offer.html');
$TBS->MergeBlock('reqDetails', $offer->reqDetails);
$TBS->MergeBlock('manDetails', $offer->manDetails);

$TBS->MergeBlock('stores', $stores);
$TBS->MergeBlock('similar', $similar);

$TBS->Show();
?>
<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$typeID = $_GET['typeID'];
$tpl->typeName = Db::qColumn("SELECT `typeName` FROM  `invTypes` WHERE  `typeID` = ?", array($typeID));

$offers = Db::q("
    SELECT offerID
    FROM lpOffers
    WHERE typeID LIKE :typeID", 
    array(':typeID'=>$typeID));

// If there is only one offer, no need to list it, go directly to offer page
if (count($offers) === 1) {
    header('Location: '.BASE_PATH.'offer/'.$offers[0]['offerID'].'/');
    die();
}

foreach ($offers AS $id => $data) {
    $offers[$id] = (new LpOffer($data['offerID']))->calc(); }

$tpl->offers = $offers;
$tpl->display('item.html');
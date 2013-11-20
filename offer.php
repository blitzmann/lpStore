<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$offerID = $_GET['offerID'];
$offer = new LpOffer($offerID);
$offer->calc('sell');

$TBS->LoadTemplate('offer.html');
$TBS->MergeBlock('reqDetails', $offer->reqDetails);
$TBS->MergeBlock('manDetails', $offer->manDetails);

$TBS->MergeBlock('stores', $offer->getStores());
$TBS->MergeBlock('similar', $offer->getSimilar());

$TBS->Show();
?>
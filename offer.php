<?php

require_once 'config.php'; 

# @todo: filter
$offerID = $_GET['offerID'];

$tpl->offer = new LpOffer($offerID);
$tpl->offer->calc('sell');

$tpl->display('offer.html');
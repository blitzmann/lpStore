<?php

require_once 'config.php'; 

$offerID = filter_input(INPUT_GET, 'offerID', FILTER_VALIDATE_INT);

$tpl->offer = new LpOffer($offerID);
$tpl->offer->calc('sell');

$tpl->display('offer.html');
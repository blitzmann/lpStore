<?php

require_once 'config.php'; 

$tpl->success = null;

if (isset($_POST['prefSave'])) {
    try {
        Prefs::setRegion(filter_input(INPUT_POST, 'region', FILTER_VALIDATE_INT));
        
        switch ($_POST['marketMode']) {
            case 'sell':
                Prefs::setMarketMode('sell','sell','sell');
                break;
            case 'buy':
                Prefs::setMarketMode('buy','sell','sell');
                break;
            case 'adv':
                Prefs::setMarketMode(
                            ($_POST['offerItem'] === 'buy' ? 'buy' : 'sell'),
                            ($_POST['reqItems']  === 'buy' ? 'buy' : 'sell'),
                            ($_POST['matItems']  === 'buy' ? 'buy' : 'sell'));
                break;
            default:
                throw new Exception('Invalid Form Data');
        }
        $tpl->success = Prefs::save();
    } catch (Exception $e) {
        $tpl->success = false;
        $tpl->msg = $e->getMessage();
    }
}

// Set radio button default
if (array(Prefs::get('marketOffer'), Prefs::get('marketReq'), Prefs::get('marketMat')) === array('sell','sell','sell')) {
    $tpl->radio = 'sell'; }
else if (array(Prefs::get('marketOffer'), Prefs::get('marketReq'), Prefs::get('marketMat')) === array('buy','sell','sell')) {
    $tpl->radio = 'buy'; }
else { $tpl->radio = 'adv'; }

$tpl->display('preferences.html');
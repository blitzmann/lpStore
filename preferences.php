<?php

require_once 'config.php'; 

$tpl->success = null;

if (isset($_POST['prefSave'])) {
    try {
        # @todo: set region
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
                            ($_POST['reqItems'] === 'buy' ? 'buy' : 'sell'),
                            ($_POST['matItems'] === 'buy' ? 'buy' : 'sell'));
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

$tpl->display('preferences.html');
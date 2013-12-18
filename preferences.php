<?php

require_once 'config.php'; 

$tpl->success = null;

if (isset($_POST['prefSave'])) {
    try {
        # @todo: set region
        switch ($_POST['marketMode']) {
            case 'sell':
                Prefs::setMarketMode(1,1,1);
                break;
            case 'buy':
                Prefs::setMarketMode(2,1,1);
                break;
            case 'adv':
                # @todo: filter
                Prefs::setMarketMode(
                            $_POST['offerItem'],
                            $_POST['reqItems'],
                            $_POST['matItems']);
                break;
            default:
                throw new Exception('Invalid Form Data');
        }
        $tpl->success = true;
    } catch (Exception $e) {
        $tpl->success = false;
        $tpl->msg = $e->getMessage();
    }
}

$tpl->display('preferences.html');
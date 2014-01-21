<?php
// This file handles requests for JSON data
# @todo: if query is required, don't exit. Pass exception / display error 
require_once "config.php";

if (isset($_GET['request'])) {
    $request = $_GET['request'];
    $query   = ($_GET['query'] === '*' ? '%' : $_GET['query']);

    switch ($request) {
        case 'type':
            if (!$query || $query == '%') { exit(); }
            echo (new Query_Types($query))->json();
            break;
        case 'offer':
            if (!$query || $query == '%') { exit(); }
            // @todo: add options for region/market modes
            $offerID = filter_input(INPUT_GET, 'query', FILTER_VALIDATE_INT);
            echo json_encode((new LpOffer($offerID))->calc());
            break;
        case 'corps':
            echo (new Query_Corps($query))->json();
            break;
        case 'system':
            if (!$query || $query == '%') { exit(); }
            echo (new Query_Systems($query))->json();
            break;
        case 'store':
            // @todo: include corp json file
            break;
        default:
            break;
    }
    
    exit();
}

$tpl->display('json.html');
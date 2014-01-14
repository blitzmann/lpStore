<?php

/*
    This file manages mostly ajax requests, but can also be included to get 
    info from the $json array.
    
*/    

$json_sql = array(
'search' => <<<'SEARCH'
    SELECT a.corporationID AS id, b.itemName AS name, 'corporation' AS type
    FROM lpStore a 
    INNER JOIN invUniqueNames b ON (a.corporationID = b.itemID AND b.groupID = 2) 
    WHERE itemName LIKE :query
    GROUP BY a.corporationID 
    
    UNION
    
    SELECT a.typeID AS id, b.typeName AS name, 'item' AS type
    FROM lpOffers a 
    INNER JOIN invTypes b ON (a.typeID = b.typeID) 
    WHERE typeName LIKE :query
SEARCH
);

function doJson($request, $query){
    global $json_sql;
    $query = ($query === '*' ? '%' : $query);
    
    $json = array();
    switch ($request) {
        case 'search':
            $query = '%'.$query.'%'; // Add wildcards
            foreach (Db::q($json_sql[$request], array(':query' => $query)) AS $result){
                $json[] = array(
                    'value' => $result['name'],
                    'id'    => $result['id'],
                    'type'  => $result['type']
                );
            }
            break;
        default:
            break;
    }
    
    return $json;
}

if (isset($_GET['noinclude'])){ // if accessing via ajax /json/blah/thing.json
    require_once "config.php";
    # @todo: try...catch

    echo json_encode(doJson($_GET['request'], $_GET['query'])); 
}

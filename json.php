<?php

/*
    This file manages mostly ajax requests, but can also be included to get 
    info from the $json array.
    
*/    

$json_sql = array(
 'faction'=><<<'FAC'
    SELECT a.corporationID, b.factionID, c.itemName AS corpName, f.itemName AS facName
    FROM `lpStore` a 
    INNER JOIN crpNPCCorporations b ON (b.corporationID = a.corporationID) 
    INNER JOIN invUniqueNames c ON (a.corporationID = c.itemID AND c.groupID = 2)
    INNER JOIN invUniqueNames f ON (b.factionID = f.itemID)
    WHERE b.factionID LIKE ?
    GROUP BY a.corporationID 
    ORDER BY facName, corpName ASC
FAC
, 'search' => <<<'SEARCH'
    SELECT a.*, b.itemName 
    FROM lpStore a 
    INNER JOIN invUniqueNames b ON (a.corporationID = b.itemID AND b.groupID = 2) 
    WHERE itemName LIKE ?
    GROUP BY a.corporationID 
    ORDER BY b.itemName ASC LIMIT 0,20
SEARCH
);

function doJson($request, $query){
    global $json_sql;
    $query = ($query === '*' ? '%' : $query);
    
    $json = array();
    switch ($request) {
        # @todo: should this really return corps?
        case 'faction':
            foreach (Db::q($json_sql[$request], array($query)) AS $result){
                if (!isset($json[$result['factionID']]['name'])) {
                    $json[$result['factionID']]['name'] = $result['facName']; }
                $json[$result['factionID']]['corps'][$result['corporationID']] = $result['corpName']; }
            break;
        case 'search':
            $query = '%'.$query.'%'; // Add wildcards
            foreach (Db::q($json_sql[$request], array($query)) AS $result){
                $json[] = array(
                    'value' => $result['itemName'],
                    'id' => $result['corporationID']
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
    # @todo: filter
    //var_dump($_GET);
    echo json_encode(doJson($_GET['request'], $_GET['query'])); 
}

<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$factionID = $_GET['factionID'];
$factionName = $DB->q1("SELECT `itemName` FROM  `invUniqueNames` WHERE  `itemID` = ?", array($factionID));

$corps = $DB->qa("
    SELECT a.corporationID, b.factionID, c.itemName AS corpName, d.itemName AS facName, count(*) AS num
    FROM `lpStore` a 
    INNER JOIN crpNPCCorporations b ON (b.corporationID = a.corporationID) 
    INNER JOIN invUniqueNames c ON (a.corporationID = c.itemID AND c.groupID = 2)
    INNER JOIN invUniqueNames d ON (b.factionID = d.itemID)
    WHERE b.factionID = ?
    GROUP BY a.corporationID 
    ORDER BY c.itemName ASC", array($factionID));

$TBS->LoadTemplate('template/faction.html');
$TBS->MergeBlock('corp_blk', $corps);

$TBS->Show();
?>
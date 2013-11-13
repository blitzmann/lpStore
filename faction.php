<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$factionID = $_GET['factionID'];
$factionName = Db::qColumn("SELECT `itemName` FROM  `invUniqueNames` WHERE  `itemID` = ?", array($factionID));

$corps = Db::q("
    SELECT a.corporationID, b.factionID, c.itemName AS corpName, d.itemName AS facName, count(*) AS num
    FROM `lpStore` a 
    INNER JOIN crpNPCCorporations b ON (b.corporationID = a.corporationID) 
    INNER JOIN invUniqueNames c ON (a.corporationID = c.itemID AND c.groupID = 2)
    INNER JOIN invUniqueNames d ON (b.factionID = d.itemID)
    WHERE b.factionID = ?
    GROUP BY a.corporationID 
    ORDER BY c.itemName ASC", array($factionID));

$TBS->LoadTemplate('faction.html');
$TBS->MergeBlock('corp_blk', $corps);

$TBS->Show();
?>
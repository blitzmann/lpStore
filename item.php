<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$typeID = $_GET['typeID'];
$typeName = $DB->q1("SELECT `typeName` FROM  `invTypes` WHERE  `typeID` = ?", array($typeID));

$TBS->LoadTemplate('item.html');
$TBS->Show();
?>
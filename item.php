<?php

require_once 'config.php'; 
require_once 'json.php';

# @todo: filter
$typeID = $_GET['typeID'];
$tpl->typeName = Db::qColumn("SELECT `typeName` FROM  `invTypes` WHERE  `typeID` = ?", array($typeID));

$tpl->display('item.html');
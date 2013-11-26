<?php

require_once 'config.php'; 

# @todo: filter
$corpID = $_GET['corporation'];

$tpl->corpName = Db::qColumn("SELECT `itemName` FROM `invUniqueNames` WHERE `itemID` = ?", array($corpID));
$tpl->lpStore = new LpStore($corpID);    

$tpl->display('corporation.html');
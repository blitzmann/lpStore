<?php

require_once 'config.php'; 

$factions = (new Query_Factions())->execute();

$tpl->primary   = array_slice($factions, 0, 4, true);
$tpl->secondary = array_slice($factions, 4, null, true);

$tpl->display('index.html');
<?php

# get rid of this, use new query classes
require_once 'config.php'; 
require_once 'json.php';

# We take the factions and then sort by ID. Slice array to get pri/sec factions
# we could also do it alphabetically, but whatev
$factions = doJson("faction", "*");
ksort($factions);

$tpl->primary   = array_slice($factions, 0, 4, true);
$tpl->secondary = array_slice($factions, 4, null, true);

$tpl->display('index.html');
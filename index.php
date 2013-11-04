<?php

require_once 'config.php'; 
require_once 'json.php';

# We take the factions and then sort by ID. Slice array to get pri/sec factions
# we could also do it alphabetically, but whatev
$factions = doJson("faction", "*");
ksort($factions);

$primary   = array_slice($factions, 0, 4, true);
$secondary = array_slice($factions, 4, null, true);

$TBS->LoadTemplate('template/index.html');
$TBS->MergeBlock('pri_blk', $primary);
$TBS->MergeBlock('sec_blk', $secondary);

$TBS->Show();
?>

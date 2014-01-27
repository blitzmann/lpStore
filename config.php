<?php

// comment this out for local debug copy
require_once 'Savant3.php';

# taken from http://stackoverflow.com/a/12583387/788054
# TBS cannot use DEFINE(), so these are variables
define('ABS_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('BASE_PATH', '/'.substr(dirname(__FILE__),strlen($_SERVER['DOCUMENT_ROOT'])).'/');

function __autoload($class) {
   require_once('lib/' . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php');
}

Emdr::$regions = json_decode(file_get_contents(ABS_PATH.'/emdr/regions.json'),true);
Prefs::getInstance();

$tpl = new Savant3();
$tpl->addPath('template', 'templates/bootstrap');
$tpl->addPath('resource', 'lib');

$tpl->siteTime = new Timer();
$tpl->emdrDown = false;
# https://forums.eveonline.com/default.aspx?g=posts&m=2508255

$regions = json_decode(file_get_contents(dirname(__FILE__).'/emdr/regions.json'),true);
Emdr::setRegion(Prefs::get('region'));

# If price of Tritanium is more than 12 hours old, then something has happened with EMDR consumer
# Set template variable to display warning to user when this happens
if (time() - (new Price(Emdr::get(34)))->generatedAt > 60*60*12) {
    $tpl->emdrDown = true; }
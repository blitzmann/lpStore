<?php

# taken from http://stackoverflow.com/a/12583387/788054
# TBS cannot use DEFINE(), so these are variables
$ABS_PATH  = str_replace('\\', '/', dirname(__FILE__)) . '/';
$BASE_PATH = '/'.substr(dirname(__FILE__),strlen($_SERVER['DOCUMENT_ROOT'])).'/';

function __autoload($class) {
   require_once('lib/' . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php');
}

$dsn_config = parse_ini_file('/home/http/private/db-eve-latest.ini', true);
$DB         = new Db($dsn_config, parse_ini_file('/home/http/private/auth-eve.ini'));

define('DATABASE', $dsn_config['dsn_opts']['dbname']); 
define('LPDB',     '0.7.1'); # https://forums.eveonline.com/default.aspx?g=posts&m=2508255

$regions = json_decode(file_get_contents(dirname(__FILE__).'/emdr/regions.json'),true);

Emdr::setRegion(10000002);

$TBS = new Template('templates', 'clean');

?>
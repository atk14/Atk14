#!/usr/bin/env php
<?php
/**
* Dumps configuration constants.
*
* You can check out how configuration varies in different environments.
*
*  $ ATK14_ENV=PRODUCTION ./scripts/dump_settings.php
*  $ ATK14_ENV=DEVELOPMENT ./scripts/dump_settings.php
*  $ ATK14_ENV=TEST ./scripts/dump_settings.php
*
* Also you can retrieve the value of the given constant:
* $ ./scripts/dump_settings.php ATK14_APPLICATION_NAME
*/

require_once(dirname(__FILE__)."/load.inc");

$constants = array_merge(array(
	"DEVELOPMENT" => DEVELOPMENT,
	"PRODUCTION" => PRODUCTION,
	"TEST" => TEST,
),$__CONFIG_CONSTANTS__);

foreach($constants as &$c){	if(is_bool($c)){ $c = $c ? "true" : "false"; } }

if(isset($argv[1])){
	echo isset($constants[$argv[1]]) ? $constants[$argv[1]]."\n" : "";
	exit;
}

print_r($constants);

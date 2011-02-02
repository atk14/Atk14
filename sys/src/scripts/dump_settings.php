#!/usr/bin/env php
<?
/**
* Dumps configuration constants.
*
* You can check out how configuration varies in different environments.
*
*  $ ATK14_ENV=PRODUCTION ./scripts/dump_settings.php
*  $ ATK14_ENV=DEVELOPMENT ./scripts/dump_settings.php
*  $ ATK14_ENV=TEST ./scripts/dump_settings.php
*/

require_once(dirname(__FILE__)."/load.inc");

print_r($__CONFIG_CONSTANTS__);

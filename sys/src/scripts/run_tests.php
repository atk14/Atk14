#!/usr/bin/env php
<?
require_once(dirname(__FILE__)."/load.inc");

$directory_with_tests = $ATK14_GLOBAL->getApplicationPath()."/../test";
$script_directory = dirname(__FILE__);

$command = "cd $directory_with_tests/models/; /usr/bin/env php $script_directory/run_unit_tests.php";
echo `$command`;

$command = "cd $directory_with_tests/controllers/; /usr/bin/env php $script_directory/run_unit_tests.php";
echo `$command`;

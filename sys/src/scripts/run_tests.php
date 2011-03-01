#!/usr/bin/env php
<?php
/**
* Run application`s tests.
*
* Due to some backlog must be run this way:
* 	$ php ./scripts/run_tests.php
*
* This will probably fail:
* 	$ ./scripts/run_tests.php
*/

require_once(dirname(__FILE__)."/load.inc");

$directory_with_tests = $ATK14_GLOBAL->getApplicationPath()."/../test";
$script_directory = dirname(__FILE__);

foreach(array("models","controllers") as $d){
	$command = "cd $directory_with_tests/$d/; $script_directory/run_unit_tests.php";
	echo "-----------\ngonna run:\n$command\n\n";
	passthru($command);
}



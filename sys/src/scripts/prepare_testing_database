#!/usr/bin/env php
<?php
define("TEST",true);
require_once(dirname(__FILE__)."/load.inc");

$dbname = $dbmole->getDatabaseName();

echo "Shall I wipe out all objects in the database $dbname? Hit y, if so... ";

$fh = fopen('php://stdin', 'r');
if(trim(fgets($fh,1024))!="y"){
	echo "Bye, bye\n";
	exit;
}

foreach($dbmole->selectIntoArray("SELECT tablename FROM pg_tables WHERE schemaname='public'") as $table){
	$dbmole->doQuery("DROP TABLE $table CASCADE");
}

foreach($dbmole->selectIntoArray("SELECT relname FROM pg_statio_user_sequences") as $sequence){
	$dbmole->doQuery("DROP SEQUENCE $sequence CASCADE");
}

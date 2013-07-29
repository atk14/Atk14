<?php
// struktura testovaci databaze je k dispozici v souboru testing_structures.sql
require("../../dbmole/dbmole.php");
require("../../dbmole/pgmole.php");
require("../../string/load.php");

require("../load.php");
require("./test_table.php");
require("./article.php");
require("./image.php");
require("./author.php");

function &dbmole_connection($dbmole){
	static $connection;
	//if($dbmole->getDatabaseType()=="postgresql" && $dbmole->getConfigurationName()=="default"){
		if(!isset($connection)){
			$connection = pg_connect("dbname=test user=test password=test");
		}
		return $connection;
	//}
}

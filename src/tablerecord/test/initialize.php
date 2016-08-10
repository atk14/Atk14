<?php
// struktura testovaci databaze je k dispozici v souboru testing_structures.sql
require("../../dbmole/dbmole.php");
require("../../dbmole/pgmole.php");
require("../../dbmole/mysqlmole.php");
require("../../string4/load.php");
require("../../files/load.php");
require("../../class_autoload/load.php");

require("../load.php");
class_autoload(__DIR__ . "/models/");

function &dbmole_connection($dbmole){
	static $connections = array();

	if($dbmole->getDatabaseType()=="postgresql"){
		if(!isset($connections["postgresql"])){
			$connections["postgresql"] = pg_connect("dbname=test user=test password=test");
		}
		return $connections["postgresql"];
	}

	if($dbmole->getDatabaseType()=="mysql"){
		if(!isset($connections["mysql"])){
			$connections["mysql"] = mysqli_connect("127.0.0.1","test","test");
			$connections["mysql"]->select_db("test");
		}
		return $connections["mysql"];
	}
}

// Creating testing structures
$GLOBALS["dbmole"] = PgMole::GetInstance();
$GLOBALS["dbmole"]->doQuery(file_get_contents(__DIR__."/structures.postgresql.sql"));

<?php
// struktura testovaci databaze je k dispozici v souboru testing_structures.sql
require("../../dbmole/dbmole.php");
require("../../dbmole/pgmole.php");
require("../../dbmole/mysqlmole.php");
require("../../string4/load.php");
require("../../files/load.php");

require("../load.php");
require("./test_table.php");
require("./my_test_table.php");
require("./article.php");
require("./image.php");
require("./author.php");
require("./redactor.php");
require("./string_like.php");
require("./string_much_like.php");
require("./int_like.php");

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
$pg = PgMole::GetInstance();
$pg->doQuery(file_get_contents(__DIR__."/structures.postgresql.sql"));

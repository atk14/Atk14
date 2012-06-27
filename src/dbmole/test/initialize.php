<?php
require("../dbmole.inc");
require("../pgmole.inc");
require("../oraclemole.inc");
require("../mysqlmole.inc");
require("../../stopwatch/stopwatch.inc");

function &dbmole_connection(&$dbmole){
	$out = null;

	switch($dbmole->getDatabaseType()){
		case "mysql":
			$out = mysql_connect("localhost","test","test");
			mysql_select_db("test",$out);
			break;

		case "postgresql":
			$out = pg_connect("dbname=test host=localhost user=test password=test");
			break;

		case "oracle":
			$out = OCILogon("test","test","test"); // user, password, sid
			break;
	}

	return $out;
}
 
function dbmole_error_handler(&$dbmole){
	$dbmole->_ErrorRaised = false;

	throw new Exception("DbMole error: ".$dbmole->getErrorMessage());
}

DbMole::RegisterErrorHandler("dbmole_error_handler");

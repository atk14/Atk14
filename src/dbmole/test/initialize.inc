<?php
require("../dbmole.inc");
require("../pgmole.inc");
require("../oraclemole.inc");
require("../mysqlmole.inc");

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

			putenv("ORACLE_BASE=/opt/oracle");
			putenv("ORACLE_HOME=/opt/oracle/db_1");
			putenv('NLS_LANG=American_America.EE8MSWIN1250');
			putenv("NLS_SORT=Czech");
			putenv("NLS_DATE_FORMAT=YYYY-MM-DD HH24:MI:SS");
			putenv('PATH='.getenv("PATH").':'.getenv("ORACLE_HOME").'/bin');

			$out = OCILogon("test","test","test"); // user, password, sid

			break;
	}

	return $out;
}

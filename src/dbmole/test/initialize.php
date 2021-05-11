<?php
define("TEST",true);
define("SENDMAIL_DO_NOT_SEND_MAILS",true);

require(__DIR__."/../dbmole.php");
require(__DIR__."/../pgmole.php");
require(__DIR__."/../oraclemole.php");
require(__DIR__."/../mysqlmole.php");
require(__DIR__."/../../stopwatch/stopwatch.php");
require(__DIR__."/../../files/load.php");
require(__DIR__."/../../sendmail/load.php");
require(__DIR__."/../../translate/load.php");

require(__DIR__."/connections_and_handler.php");

// === Creating testing table in postgresql
$pg = PgMole::GetInstance();
$pg->doQuery(file_get_contents(__DIR__."/structures.postgresql.sql"));

// === Creating testing table in mysql
$my = MysqlMole::GetInstance();
$script = file_get_contents(__DIR__."/structures.mysql.sql");
// dropping table
preg_match('/\n(DROP TABLE.*?);/s',$script,$matches);
$my->doQuery($matches[1]);
// creating table
preg_match('/\n(CREATE TABLE.*?);/s',$script,$matches);
$my->doQuery($matches[1]);


class ProxyDbMole extends DbMole {

	function __construct($configuration_name = "default"){
		parent::__construct($configuration_name);
	}

	function parseVersion($version,$options){
		return $this->_parseVersion($version,$options);
	}
}

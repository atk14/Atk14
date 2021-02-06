<?php
define("TEST",true);

define("SESSION_STORER_DEFAULT_SESSION_NAME","session");
define("SESSION_STORER_COOKIE_NAME_SESSION","%session_name%");
define("SESSION_STORER_COOKIE_NAME_CHECK","check");
define("SESSION_STORER_SESSION_MAX_LIFETIME",60 * 60 * 24 * 1);  // a day
define("CURRENT_TIME",time());
require("../../stringbuffer/load.php");
require("../../http/load.php");
require("../../string4/load.php");
require("../sessionstorer.php");

// UF! we need dbmole
require(dirname(__FILE__)."/../../dbmole/dbmole.php");
require(dirname(__FILE__)."/../../dbmole/pgmole.php");
require(dirname(__FILE__)."/../../dbmole/test/connections_and_handler.php");

// rectreating database structures
$dbmole = PgMole::GetInstance();
$dbmole->doQuery(file_get_contents(__DIR__."/drop_structures.postgresql.sql"));
$dbmole->doQuery(file_get_contents(__DIR__."/../structures.postgresql.sql"));

$HTTP_REQUEST->setRemoteAddr("127.0.0.1");

<?php
define("TEST",true);

define("SESSION_STORER_COOKIE_NAME_SESSION","session");
define("SESSION_STORER_COOKIE_NAME_CHECK","check");
define("CURRENT_TIME",time());
require("../../stringbuffer/stringbuffer.php");
require("../../http/load.php");
require("../../string/load.php");
require("../sessionstorer.php");

// UF! we need dbmole
require(dirname(__FILE__)."/../../dbmole/dbmole.php");
require(dirname(__FILE__)."/../../dbmole/pgmole.php");
require(dirname(__FILE__)."/../../dbmole/test/connections_and_handler.php");

$dbmole = PgMole::GetInstance();
$HTTP_REQUEST->setRemoteAddr("127.0.0.1");

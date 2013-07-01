<?php
define("TEST",true);

define("SESSION_STORER_COOKIE_NAME_SESSION","session");
define("SESSION_STORER_COOKIE_NAME_CHECK","check");
require("../../stringbuffer/stringbuffer.inc");
require("../../http/load.inc");
require("../../string/load.php");
require("../sessionstorer.inc");

// UF! we need dbmole
require(dirname(__FILE__)."/../../dbmole/dbmole.inc");
require(dirname(__FILE__)."/../../dbmole/pgmole.inc");
require(dirname(__FILE__)."/../../dbmole/test/connections_and_handler.php");

$dbmole = PgMole::GetInstance();
$HTTP_REQUEST->setRemoteAddr("127.0.0.1");

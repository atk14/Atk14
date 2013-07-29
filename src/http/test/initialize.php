<?php
error_reporting(255);

$_SERVER["REMOTE_ADDR"] = "1.2.3.4";
$_SERVER["HTTP_HOST"] = "www.test.cz";

define("TEST",true);
define("TEMP",dirname(__FILE__)."/temp/");
include("../httpuploadedfile.php");
include("../../stringbuffer/stringbuffer.php");
include("../../url_fetcher/url_fetcher.php");
include("../../files/load.php");
include("../../miniyaml/miniyaml.php");
include("../load.php");

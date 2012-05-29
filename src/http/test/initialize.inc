<?php
error_reporting(255);

$_SERVER["REMOTE_ADDR"] = "1.2.3.4";
$_SERVER["HTTP_HOST"] = "www.test.cz";

define("TEST",true);
define("TEMP",dirname(__FILE__)."/temp/");
include("../httpuploadedfile.inc");
include("../../stringbuffer/stringbuffer.inc");
include("../../url_fetcher/url_fetcher.inc");
include("../../files/files.inc");
include("../../miniyaml/miniyaml.inc");
include("../load.inc");

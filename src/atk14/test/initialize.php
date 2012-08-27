<?php
// please, see file config/settings.php for some more options
define("TEST",true);

define("PATH_ATK14_APPLICATION",dirname(__FILE__)."/app/");
define("ATK14_DOCUMENT_ROOT",dirname(__FILE__)."/");

define("ATK14_HTTP_HOST","www.testing.cz");
$GLOBALS["_SERVER"]["HTTP_HOST"] = "www.testing.cz";
$_GET = array();

require("../../../load.php");

require(dirname(__FILE__)."/app/forms/test_form.php");


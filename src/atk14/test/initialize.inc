<?php
// please, see file config/local_settings.inc for some more options
define("TEST",true);

define("PATH_ATK14_APPLICATION",dirname(__FILE__)."/app/");
define("ATK14_DOCUMENT_ROOT",dirname(__FILE__)."/");

$GLOBALS["_SERVER"]["HTTP_HOST"] = "www.testing.cz";
$_GET = array();

require("../../load.inc");

require(dirname(__FILE__)."/app/forms/test_form.inc");
require("app/controllers/application.inc");
require("app/controllers/testing_controller.inc");
require("app/controllers/multiple_before_filters_controller.inc");
require("app/controllers/multiple_after_filters_controller.inc");

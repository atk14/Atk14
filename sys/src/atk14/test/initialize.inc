<?php
define("TEST",true);

define("PATH_ATK14_APPLICATION",dirname(__FILE__)."/app/");
define("ATK14_DOCUMENT_ROOT",dirname(__FILE__)."/");
define("TEMP",dirname(__FILE__)."/temp/");

$GLOBALS["_SERVER"]["HTTP_HOST"] = "www.testing.cz";
$_GET = array();

//require("../../forms/forms.php");

require("../../load.inc");

/*
require("../../sessionstorer.inc");
require("../../stringbuffer.inc");
require("../../http/load.inc");
//require("../../functions.inc");
require("../../dates.inc");
require("../../date.inc");
require("../../dictionary.inc");
require("../../smarty/libs/Smarty.class.php");

require("app/controllers/application.inc");
require("app/controllers/testing_controller.inc");
require("app/controllers/multiple_before_filters_controller.inc");
require("app/controllers/multiple_after_filters_controller.inc");
*/


//require(dirname(__FILE__)."/../../load.inc");

require(dirname(__FILE__)."/app/forms/test_form.inc");
require("app/controllers/application.inc");
require("app/controllers/testing_controller.inc");
require("app/controllers/multiple_before_filters_controller.inc");
require("app/controllers/multiple_after_filters_controller.inc");

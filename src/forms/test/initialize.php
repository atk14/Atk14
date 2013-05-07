<?php
define("TEST",true);
define("FORMS_ENABLE_EXPERIMENTAL_HTML5_FEATURES",false); // this features break up tests

require_once("../../class_autoload/class_autoload.inc");
require_once("../../atk14/atk14_locale.inc");
require_once("../../dates/load.php");
require_once("../../stringbuffer/stringbuffer.inc");
require_once("../../http/load.inc");
require_once("../../functions.inc");
require_once("../load.php");
require_once("../../xmole/xmole.php");
require_once("./custom_fields/url_field.php");

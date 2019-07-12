<?php
defined("FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4") || define("FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4",false);

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/widgets.php');
require_once(dirname(__FILE__).'/fields.php');
class_autoload(dirname(__FILE__).'/widgets/');
class_autoload(dirname(__FILE__).'/fields/');
require_once(dirname(__FILE__).'/forms.php');

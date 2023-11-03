<?php
defined("FORMS_MARKUP_TUNED_FOR_BOOTSTRAP3") || define("FORMS_MARKUP_TUNED_FOR_BOOTSTRAP3",defined("USING_BOOTSTRAP3") ? constant("USING_BOOTSTRAP3") : false);
defined("FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4") || define("FORMS_MARKUP_TUNED_FOR_BOOTSTRAP4",defined("USING_BOOTSTRAP4") ? constant("USING_BOOTSTRAP4") : false);
defined("FORMS_MARKUP_TUNED_FOR_BOOTSTRAP5") || define("FORMS_MARKUP_TUNED_FOR_BOOTSTRAP5",defined("USING_BOOTSTRAP5") ? constant("USING_BOOTSTRAP5") : false);

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/widgets.php');
require_once(dirname(__FILE__).'/fields.php');
class_autoload(dirname(__FILE__).'/widgets/');
class_autoload(dirname(__FILE__).'/fields/');
require_once(dirname(__FILE__).'/forms.php');

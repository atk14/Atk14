<?php
/**
 * Whether to enable some HTML5 features or not
 */
@define("FORMS_ENABLE_EXPERIMENTAL_HTML5_FEATURES",true);

require_once(dirname(__FILE__).'/widgets.php');
require_once(dirname(__FILE__).'/fields.php');
class_autoload(dirname(__FILE__).'/widgets/');
class_autoload(dirname(__FILE__).'/fields/');
require_once(dirname(__FILE__).'/forms.php');

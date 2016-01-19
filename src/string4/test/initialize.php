<?php
define("DEFAULT_CHARSET","UTF-8");
define("TRANSLATE_USE_ICONV",false);

if(PHP_VERSION_ID < 50600){
	function_exists('iconv_set_encoding') && iconv_set_encoding('internal_encoding',DEFAULT_CHARSET);
	function_exists("mb_internal_encoding") && mb_internal_encoding(DEFAULT_CHARSET);
} else {
	ini_set('default_charset', DEFAULT_CHARSET);
}

require("../load.php");
require("../../translate/translate.php");

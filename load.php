<?php
/**
* There are a few components needs to be loaded.
* Atk14 connects these components to the right order.
*/
error_reporting(255);

define("ATK14_VERSION","1.4");

// We need to load Atk14Utils first, then using it determine environment and then finally load the rest of ATK14...
// HTTP* classes give us right advices about environment & configuration
require_once(__DIR__."/src/stringbuffer/load.php");
require_once(__DIR__."/src/files/load.php");
require_once(__DIR__."/src/http/load.php");
require_once(__DIR__."/src/atk14/atk14_utils.php");
require_once(__DIR__."/src/functions.php");
Atk14Utils::DetermineEnvironment();

// Loading the main configuration file (local_config/settings.php or config/settings.php)
$_document_root = defined("ATK14_DOCUMENT_ROOT") ? ATK14_DOCUMENT_ROOT : __DIR__."/..";
require_once(file_exists("$_document_root/local_config/settings.php") ? "$_document_root/local_config/settings.php" : "$_document_root/config/settings.php");
require_once(__DIR__."/default_settings.php");

// Loading framework libraries.
require_once(__DIR__."/src/class_autoload/load.php");
require_once(__DIR__."/src/string4/load.php");
require_once(__DIR__."/src/translate/load.php");
require_once(__DIR__."/src/dictionary/load.php");
require_once(__DIR__."/src/miniyaml/load.php");
require_once(__DIR__."/src/dates/load.php");
require_once(__DIR__."/src/xmole/load.php");
require_once(__DIR__."/src/stopwatch/load.php");
require_once(__DIR__."/src/logger/load.php");
require_once(__DIR__."/src/lock/load.php");
if(ATK14_USE_SMARTY3){
	require_once(__DIR__."/src/smarty3/libs/SmartyBC.class.php");
}else{
	require_once(__DIR__."/src/smarty/libs/Smarty.class.php");
}
require_once(__DIR__."/src/dbmole/load.php");
require_once(__DIR__."/src/tablerecord/load.php");
require_once(__DIR__."/src/sessionstorer/load.php");
require_once(__DIR__."/src/packer/load.php");
require_once(__DIR__."/src/sendmail/load.php");
require_once(__DIR__."/src/forms/load.php");
require_once(__DIR__."/src/url_fetcher/load.php");
require_once(__DIR__."/src/atk14/load.php");

// Loading the application's stuff.
//
// Loading model classes, field (and widget) classes and external (3rd party) libs.
// In every directory class_autoload() is applied. I believe it can do a lot.
// But everywhere the load.php file is optional.
foreach(array("lib","app/models","app/fields","app/widgets") as $_d_){
	class_autoload(ATK14_DOCUMENT_ROOT."/$_d_/");
	($_f_ = atk14_find_file(ATK14_DOCUMENT_ROOT."/$_d_/load.php")) && require_once($_f_);
}

foreach(array(
	// forms are now loaded in Atk14Utils::LoadControllers()
	//ATK14_DOCUMENT_ROOT."/app/forms/application_form.php",
	//ATK14_DOCUMENT_ROOT."/app/forms/form.php",

	ATK14_DOCUMENT_ROOT."/config/routers/load.php"
) as $_f_){
	($_f_ = atk14_find_file($_f_)) && require_once($_f_);
}

// global variable $dbmole holds database connection
$__db_config__ = $ATK14_GLOBAL->getDatabaseConfig();
$dbmole = DbMole::GetInstance("default",$__db_config__["adapter"]); // $dbmole = DbMole::GetInstance("default","postgresql");
unset($__db_config__);

function &dbmole_connection(&$dbmole){
	global $ATK14_GLOBAL;

	$out = null;

	if(!$d = $ATK14_GLOBAL->getDatabaseConfig($dbmole->getConfigurationName())){
		// make sure that the function custom_database_connection() exists somewhere within your application
		return custom_database_connection($dbmole);
	}

	// there is a configuration name in $dbmole->getConfigurationName()
	// it's useful when there is a need to connect to more databases

	switch($dbmole->getDatabaseType()){
		case "mysql":
			$out = mysqli_connect($d["host"], $d["username"], $d["password"], $d["database"] , $d["port"]);
			break;

		case "postgresql":
			$out = pg_connect("dbname=$d[database] ".($d["host"] ? " host=$d[host]" : "").($d["port"] ? " port=$d[port]" : "")." user=$d[username] password=$d[password]");
			break;

		case "oracle":
			// TODO
			break;
	}

	return $out;
}

function dbmole_error_handler($dbmole){
	global $ATK14_LOGGER;

	if(PRODUCTION){
		$dbmole->logErrorReport(); // logs the error into a error log
		$dbmole->sendErrorReportToEmail(ATK14_ADMIN_EMAIL); // sends an email; sending rate limit is built-in

		if($ATK14_LOGGER){
			$ATK14_LOGGER->error($dbmole->getErrorReport());
			$ATK14_LOGGER->flush();
		}
	}elseif(!TEST){
		echo "<pre>";
		echo h($dbmole->getErrorReport());
		echo "</pre>";
	}

	throw new DbMoleException(get_class($dbmole)." on ".$dbmole->getDatabaseName().": ".$dbmole->getErrorMessage());

	exit(1);
}
DbMole::RegisterErrorHandler("dbmole_error_handler");

function atk14_initialize_locale(&$lang = null){
	global $ATK14_GLOBAL;

	$locale = $ATK14_GLOBAL->getConfig("locale");

	if(is_null($lang) || !isset($locale[$lang])){
		$_keys = array_keys($locale);
		$lang = $_keys[0];
	}

	$l = $locale[$lang]["LANG"];

	putenv("LANG=$l");
	putenv("LANGUAGE=");
	setlocale(LC_MESSAGES,$l);
	setlocale(LC_ALL,$l);
	setlocale(LC_CTYPE,$l);
	setlocale(LC_COLLATE,$l);
	setlocale(LC_NUMERIC,"C"); // we need to display float like 123.456
	bindtextdomain("messages",$ATK14_GLOBAL->getApplicationPath()."/../locale/");
	bind_textdomain_codeset("messages", DEFAULT_CHARSET);
	textdomain("messages");
}

if(PHP_VERSION_ID < 50600){
	function_exists('iconv_set_encoding') && iconv_set_encoding('internal_encoding',DEFAULT_CHARSET);
	function_exists("mb_internal_encoding") && mb_internal_encoding(DEFAULT_CHARSET);
} else {
	ini_set('default_charset', DEFAULT_CHARSET);
}

// initializing locale for the default language (i.e. the first one in config/locale.yml or defined by the constant ATK14_DEFAULT_LANG)
Atk14Locale::Initialize();

// catching up assertion failures
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 1);
assert_options(ASSERT_CALLBACK, 'assert_callback');
function assert_callback($script, $line, $message) {
	$msg = "Assertion failed: Script: $script; Line: $line; Condition: $message";
	error_log($msg);
	throw new Exception($msg);
}

// on non-UTF-8 apps following hack converts UTF-8 params to DEFAULT_CHARSET
function __to_default_charset__(&$params){
	foreach($params as $key => $value){
		if(is_string($value)){
			Translate::CheckEncoding($params[$key],"UTF-8") && ($params[$key] = Translate::Trans($params[$key],"UTF-8",DEFAULT_CHARSET));
			continue;
		}
		if(is_array($value)){
			__to_default_charset__($params[$key]);
		}
	}
}
if(DEFAULT_CHARSET!="UTF-8"){
	if($HTTP_REQUEST->xhr() && isset($_POST) && is_array($_POST)){ __to_default_charset__($_POST); }
	if($HTTP_REQUEST->xhr() && isset($_GET) && is_array($_GET)){ __to_default_charset__($_GET); }
}

// Now the application is fully loaded and initialized.
// The after initialize configuration can be loaded.
if(ATK14_LOAD_AFTER_INITIALIZE_SETTINGS){
	foreach(array(
		ATK14_DOCUMENT_ROOT."/local_config/after_initialize.php",
		ATK14_DOCUMENT_ROOT."/config/after_initialize.php",
	) as $_after_initialization_config){
		if(file_exists($_after_initialization_config)){
			require($_after_initialization_config);
			break;
		}
	}
}

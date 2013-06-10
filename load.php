<?php
/**
* There are a few components needs to be loaded.
* Atk14 connects these components to the right order.
*/
error_reporting(255);

// we need to load Atk14Utils first, then using it determine environment and then finally load the rest of ATK14...
// HTTP* classes give us right advices about environment & configuration
require_once(dirname(__FILE__)."/src/stringbuffer/stringbuffer.inc");
require_once(dirname(__FILE__)."/src/files/load.php");
require_once(dirname(__FILE__)."/src/http/load.inc");
require_once(dirname(__FILE__)."/src/atk14/atk14_utils.inc");
Atk14Utils::DetermineEnvironment();

// now we are gonna to set up config constants
if(defined("ATK14_DOCUMENT_ROOT")){
	require_once(ATK14_DOCUMENT_ROOT."/config/settings.php");
}else{
	require_once(dirname(__FILE__)."/../config/settings.php");
}
require_once(dirname(__FILE__)."/default_settings.php");

// load the rest...
require_once(dirname(__FILE__)."/src/string/load.inc");
require_once(dirname(__FILE__)."/src/translate/translate.php");
require_once(dirname(__FILE__)."/src/dictionary/dictionary.inc");
require_once(dirname(__FILE__)."/src/miniyaml/miniyaml.inc");
require_once(dirname(__FILE__)."/src/dates/load.php");
require_once(dirname(__FILE__)."/src/xmole/xmole.php");
require_once(dirname(__FILE__)."/src/stopwatch/stopwatch.inc");
require_once(dirname(__FILE__)."/src/logger/logger.inc");
require_once(dirname(__FILE__)."/src/lock/lock.php");
require_once(dirname(__FILE__)."/src/json/load.inc");
if(ATK14_USE_SMARTY3){
	require_once(dirname(__FILE__)."/src/smarty3/libs/SmartyBC.class.php");
}else{
	require_once(dirname(__FILE__)."/src/smarty/libs/Smarty.class.php");
}
require_once(dirname(__FILE__)."/src/class_autoload/class_autoload.inc");
require_once(dirname(__FILE__)."/src/dbmole/dbmole.inc");
require_once(dirname(__FILE__)."/src/dbmole/pgmole.inc");
require_once(dirname(__FILE__)."/src/tablerecord/load.php");
require_once(dirname(__FILE__)."/src/sessionstorer/sessionstorer.inc");
require_once(dirname(__FILE__)."/src/packer/packer.inc");
require_once(dirname(__FILE__)."/src/sendmail/sendmail.inc");
require_once(dirname(__FILE__)."/src/forms/load.php");
require_once(dirname(__FILE__)."/src/url_fetcher/url_fetcher.inc");
require_once(dirname(__FILE__)."/src/atk14/load.inc");
require_once(dirname(__FILE__)."/src/functions.inc");

// ...and load basic application`s objects
foreach(array(
	// forms are now loaded in Atk14Utils::LoadControllers()
	//ATK14_DOCUMENT_ROOT."/app/forms/application_form.php",
	//ATK14_DOCUMENT_ROOT."/app/forms/form.php",

	ATK14_DOCUMENT_ROOT."/config/routers/load.php"
) as $_f_){
	($_f_ = atk14_find_file($_f_)) && require_once($_f_);
}

// Loading model classes, field (and widget) classes and external (3rd party) libs.
// In every directory class_autoload() is applied. I believe it can do a lot.
// But everywhere the load.php file is optional.
foreach(array("app/models","app/fields","app/widgets","lib") as $_d_){
	class_autoload(ATK14_DOCUMENT_ROOT."/$_d_/");
	($_f_ = atk14_find_file(ATK14_DOCUMENT_ROOT."/$_d_/load.php")) && require_once($_f_);
}

// global variable $dbmole holds database connection
// at the moment only postgresql is supported (why don't just support the best open source database worldwide?)
$dbmole = &PgMole::GetInstance("default");

function &dbmole_connection(&$dbmole){
	global $ATK14_GLOBAL;

	$out = null;

	if($dbmole->getConfigurationName()!="default"){
		// make sure that the function custom_database_connection() exists somewhere within your application
		return custom_database_connection($dbmole);
	}

	$d = $ATK14_GLOBAL->getDatabaseConfig();

	// there is a configuration name in $dbmole->getConfigurationName()
	// it's useful when there is a need to connect to more databases

	switch($dbmole->getDatabaseType()){
		case "mysql":
			//TODO
			break;

		case "postgresql":
			$out = pg_connect("dbname=$d[database] host=$d[host] user=$d[username] password=$d[password]");
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
		$dbmole->sendErrorReportToEmail(ATK14_ADMIN_EMAIL);
		$dbmole->logErrorReport(); // zaloguje chybu do error logu

		$response = Atk14Dispatcher::ExecuteAction("application","error500",array(
			"render_layout" => false,
			"apply_render_component_hacks" => true,
		));
		$response->flushAll();

		if($ATK14_LOGGER){
			$ATK14_LOGGER->error($dbmole->getErrorReport());
			$ATK14_LOGGER->flush();
		}
	}else{
		echo "<pre>";
		echo h($dbmole->getErrorReport());
		echo "</pre>";
	}

	exit;
}
DbMole::RegisterErrorHandler("dbmole_error_handler");

function atk14_initialize_locale(&$lang){
	global $ATK14_GLOBAL;

	$locale = $ATK14_GLOBAL->getConfig("locale");

	if(!isset($locale[$lang])){
		$_keys = array_keys($locale);
		$lang = $_keys[0];
	}

	$l = $locale[$lang]["LANG"];

	putenv("LANG=$l");
	setlocale(LC_MESSAGES,$l);
	setlocale(LC_ALL,$l);
	setlocale(LC_CTYPE,$l);
	setlocale(LC_COLLATE,$l);
	bindtextdomain("messages",dirname(__FILE__)."/../locale/");
	bind_textdomain_codeset("messages", DEFAULT_CHARSET);
	textdomain("messages");
}

function_exists("iconv_set_encoding") && iconv_set_encoding('internal_encoding',DEFAULT_CHARSET);
function_exists("mb_internal_encoding") && mb_internal_encoding(DEFAULT_CHARSET);

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
	reset($params);
	while(list($key,$value) = each($params)){
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

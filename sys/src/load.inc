<?
/**
* There are a few components needs to be loaded.
* Atk14 connects these components to the right order.
*/

error_reporting(255);

// we need to load Atk14Utils first, then using it determine environment and then finally load the rest of ATK14...
// HTTP* classes give us right advices about environment & configuration
require_once(dirname(__FILE__)."/stringbuffer.inc");
require_once(dirname(__FILE__)."/http/load.inc");
require_once(dirname(__FILE__)."/atk14/atk14_utils.inc");
Atk14Utils::DetermineEnvironment();

// now we are gonna to set up config constants
require_once(dirname(__FILE__)."/../../config/local_settings.inc");
require_once(dirname(__FILE__)."/default_settings.inc");

// load the rest...
require_once(dirname(__FILE__)."/dictionary.inc");
require_once(dirname(__FILE__)."/miniyaml.inc");
require_once(dirname(__FILE__)."/files.inc");
require_once(dirname(__FILE__)."/dates/load.inc");
require_once(dirname(__FILE__)."/xmole.inc");
require_once(dirname(__FILE__)."/stopwatch.inc");
require_once(dirname(__FILE__)."/logger.inc");
require_once(dirname(__FILE__)."/json_initialize.inc");
require_once(dirname(__FILE__)."/smarty/libs/Smarty.class.php");
require_once(dirname(__FILE__)."/class_autoload.inc");
require_once(dirname(__FILE__)."/dbmole/dbmole.inc");
require_once(dirname(__FILE__)."/dbmole/pgmole.inc");
require_once(dirname(__FILE__)."/inobj_tablerecord/inobj.inc");
require_once(dirname(__FILE__)."/inobj_tablerecord/inobj_tablerecord_base.inc");
require_once(dirname(__FILE__)."/inobj_tablerecord/inobj_tablerecord.inc");
require_once(dirname(__FILE__)."/inobj_tablerecord/inobj_tablerecord_finder.inc");
require_once(dirname(__FILE__)."/inobj_tablerecord/inobj_tablerecord_emptyfinder.inc");
require_once(dirname(__FILE__)."/sessionstorer.inc");
require_once(dirname(__FILE__)."/packer.inc");
require_once(dirname(__FILE__)."/sendmail.inc");
require_once(dirname(__FILE__)."/forms/load.inc");
require_once(dirname(__FILE__)."/atk14/load.inc");

// ...and load basic application`s objects
if(file_exists(dirname(__FILE__)."/../../app/forms/application_form.inc")){
	require_once(dirname(__FILE__)."/../../app/forms/application_form.inc");
}
if(file_exists(dirname(__FILE__)."/../../app/forms/form.inc")){
	require_once(dirname(__FILE__)."/../../app/forms/form.inc");
}

// Loading model classes, field (and widget) classes and external (3rd party) libs.
// In every directory class_autoload() is applied. I believe it can do a lot.
// But everywhere the load file is optional.
foreach(array("app/models","app/fields","lib") as $_d_){
	class_autoload(dirname(__FILE__)."/../../$_d_/");
	if(file_exists(dirname(__FILE__)."/../../$_d_/load.inc")){
		require_once(dirname(__FILE__)."/../../$_d_/load.inc");
	}
}

// global variable $dbmole holds database connection
// at the moment only postgresql is supported (why don't just support the best open source database worldwide?)
$dbmole = &PgMole::GetInstance();

function &dbmole_connection(&$dbmole){
	global $ATK14_GLOBAL;

	$out = null;

	$database_ar = $ATK14_GLOBAL->getValue("database");
	if(DEVELOPMENT){
		$d = $database_ar["development"];
	}elseif(TEST){
		$d = $database_ar["test"];
	}else{
		$d = $database_ar["production"];
	}

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
	if(PRODUCTION){
		$dbmole->sendErrorReportToEmail(ATK14_ADMIN_EMAIL);
		$dbmole->logErrorReport(); // zaloguje chybu do error logu

		$response = Atk14Dispatcher::ExecuteAction("application","error500",array(
			"render_layout" => false,
			"apply_render_component_hacks" => true,
		));
		$response->flushAll();
	}else{
		echo "<pre>";
		echo $dbmole->getErrorReport();
		echo "</pre>";
	}

	exit;
}
DbMole::RegisterErrorHandler("dbmole_error_handler");

function atk14_initialize_locale(&$lang){
	global $ATK14_GLOBAL;

	$locale = $ATK14_GLOBAL->getValue("locale");

	if(!isset($locale[$lang])){
		$_keys = array_keys($locale);
		$lang = $_keys[0];
	}

	$l = $locale[$lang]["LANG"];

	putenv("LANG=$l");
	setlocale(LC_MESSAGES,$l);
	setlocale(LC_ALL,$l);
	bindtextdomain("messages",dirname(__FILE__)."/../../locale/");
	bind_textdomain_codeset("messages", "UTF-8");
	textdomain("messages");
}

function EasyReplace($str,$replaces){
  settype($replaces,"array");
  $_replaces_keys = array();
  $_replaces_values = array();
  reset($replaces);
  while(list($key,) = each($replaces)){
    $_replaces_keys[] = $key;
    $_replaces_values[] = $replaces[$key];
  }   
  if(sizeof($_replaces_keys)==0){
    return $str;
  }   
  return str_replace($_replaces_keys,$_replaces_values,$str);
}

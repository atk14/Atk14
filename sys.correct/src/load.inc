<?
error_reporting(255);

define("APP_DOCUMENT_ROOT",dirname(__FILE__)."/../../");

require_once(dirname(__FILE__)."/dictionary.inc");
require_once(dirname(__FILE__)."/sessionstorer.inc");
require_once(dirname(__FILE__)."/miniyaml.inc");
require_once(dirname(__FILE__)."/files.inc");
require_once(dirname(__FILE__)."/stringbuffer.inc");
require_once(dirname(__FILE__)."/stopwatch.inc");
require_once(dirname(__FILE__)."/logger.inc");
require_once(dirname(__FILE__)."/http/load.inc");
require_once(dirname(__FILE__)."/json_initialize.inc");
require_once(dirname(__FILE__)."/forms/load.inc");
require_once(dirname(__FILE__)."/dbmole/dbmole.inc");
require_once(dirname(__FILE__)."/dbmole/pgmole.inc");
require_once(dirname(__FILE__)."/atk14/load.inc");
require_once(dirname(__FILE__)."/inobj_tablerecord/inobj.inc");
require_once(dirname(__FILE__)."/inobj_tablerecord/inobj_tablerecord_base.inc");
require_once(dirname(__FILE__)."/inobj_tablerecord/inobj_tablerecord.inc");
require_once(dirname(__FILE__)."/smarty/libs/Smarty.class.php");

require_once(dirname(__FILE__)."/../../app/forms/application_form.inc");
require_once(dirname(__FILE__)."/../../app/models/load.inc");

if(defined("TEST") && TEST){
	define("DEVELOPMENT",false);
	define("PRODUCTION",false);
}else{
	define("DEVELOPMENT",$HTTP_REQUEST->getRemoteAddr()=="127.0.0.1" || preg_match("/^(192\\.168\\.|10\\.|172\\.16\\.)/",$HTTP_REQUEST->getRemoteAddr()));
	define("PRODUCTION",!DEVELOPMENT);
	define("TEST",false);
}

if(TEST){
	define("LOGGER_DEFAULT_LOG_FILE",dirname(__FILE__)."/../../log/test.log");
	define("TEMP",dirname(__FILE__)."/../../tmp/test/");
}else{
	define("LOGGER_DEFAULT_LOG_FILE",dirname(__FILE__)."/../../log/application.log");
	define("TEMP",dirname(__FILE__)."/../../tmp/");
}

//$dbmole = &PgMole::GetInstance();

function &dbmole_connection(&$dbmole){
	global $ATK14_GLOBAL;

	$out = null;

	$database_ar = $ATK14_GLOBAL->getValue("database");
	if(DEVELOPMENT){
		$d = $database_ar["development"];
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
		$dbmole->sendErrorReportToEmail("jaromir.tomek@ntvage.cz");
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
?>

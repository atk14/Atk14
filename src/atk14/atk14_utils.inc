<?php
/**
 *
 * @package Atk14
 * @subpackage Core
 * @filesource
 */

/**
 *
 * @package Atk14
 * @subpackage Core
 *
 */
class Atk14Utils{

	/**
	 * Determines environment.
	 *
	 * Determines environment and depending on that defines some constants.
	 *
	 * It first checks whether some of constants TEST,DEVELOPMENT or PRODUCTION is defined.
	 *
	 * If none of them is defined it checks the system environment variable ATK14_ENV and when found it defines constants TEST,DEVELOPMENT and PRODUCTION depending on the value of ATK14_ENV
	 *
	 * When even ATK14_ENV is not defined it defines these constants depending on REMOTE_ADDRESS.
	 * For localhost or addresses in 192.168.0.0 and 172.16.0.0 or no IP(script is run from console) it defines environment as DEVELOPMENT, otherwise PRODUCTION.
	 *
	 * @static
	 */
	static function DetermineEnvironment(){
		global $HTTP_REQUEST;
		// Determining environment constants (i.e. DEVELOPMENT, TEST, PRODUCTION).
		// An existing constant has the strongest importance.
		if(defined("TEST") && TEST){
			define("DEVELOPMENT",false);
			define("PRODUCTION",false);
		}elseif(defined("DEVELOPMENT") && DEVELOPMENT){
			define("TEST",false);
			define("PRODUCTION",false);	
		}elseif(defined("PRODUCTION") && PRODUCTION){
			define("DEVELOPMENT",false);
			define("TEST",false);

		// No environment constant was defined? Checkatk14_smarty_prefiltering out the ATK14_ENV environment variable...
		}elseif(strtoupper(getenv("ATK14_ENV"))=="TEST"){
			define("TEST",true);
			define("DEVELOPMENT",false);
			define("PRODUCTION",false);
		}elseif(strtoupper(getenv("ATK14_ENV"))=="DEVELOPMENT"){
			define("TEST",false);
			define("DEVELOPMENT",true);
			define("PRODUCTION",false);
		}elseif(strtoupper(getenv("ATK14_ENV"))=="PRODUCTION"){
			define("TEST",false);
			define("DEVELOPMENT",false);
			define("PRODUCTION",true);

		// At last there is an auto determination.
		// If there is an internal remote address or there is no remote address at all (i.e. a script is running from a console),
		// environment is treat as DEVELOPMENT.
		}else{
			define("DEVELOPMENT",$HTTP_REQUEST->getRemoteAddr()=="127.0.0.1" || preg_match("/^(192\\.168\\.|10\\.|172\\.16\\.)/",$HTTP_REQUEST->getRemoteAddr()) || $HTTP_REQUEST->getRemoteAddr()=="");
			define("PRODUCTION",!DEVELOPMENT);
			define("TEST",false);
		}
	}

	/**
	 * Load all config files.
	 *
	 * Loads all config files (*.inc) in directory $ATK14_GLOBAL->getApplicationPath()/../config/
	 * Also tries to use formerly prefered directory $ATK14_GLOBAL->getApplicationPath()/conf
	 *
	 * @static
	 */
	static function LoadConfig(){
		global $ATK14_GLOBAL;
		if(!file_exists($path = $ATK14_GLOBAL->getApplicationPath()."../config/")){
			$path = $ATK14_GLOBAL->getApplicationPath()."conf/";
		}

		if(file_exists("$path/routers/")){
			class_autoload("$path/routers/");
		}

		if(file_exists($_f = $path."database.yml")){
			$ATK14_GLOBAL->setValue("database",miniYAML::Load(files::get_file_content($_f,$e,$e_s)));
		}

		if(file_exists($_f = $path."locale.yml")){
			$ATK14_GLOBAL->setValue("locale",miniYAML::Load(files::get_file_content($_f,$e,$e_s)));
		}

		$dir = opendir($path);
		while($file = readdir($dir)){
			if($file=="local_settings.inc"){ continue; } // this is ugly hack :( i need to delay loading of ./config/local_settings.inc
			if(preg_match("/.inc$/",$file) && is_file($path.$file)){
				require_once($path.$file);
				//require_once($ATK14_GLOBAL->getApplicationPath()."conf/$file");
			}
		}
		closedir($dir);
	}

	/**
	 * Loads resources for a controller and also the controller.
	 *
	 * <code>
	 * Atk14Utils::LoadControllers("help_controller");
	 * </code>
	 *
	 * This code loads all resources needed by HelpController and in the end loads the HelpController
	 *
	 * @static
	 * @param string $controller_name name of controller
	 *
	 */
	static function LoadControllers($controller_name){
		Atk14Require::Controller("ApplicationController");
		Atk14Require::Controller("_*");
		Atk14Require::Controller($controller_name);
	}

	/**
	 * Escapes string for use in javascript.
	 *
	 * @param string $content string to be escaped
	 * @return string escaped string
	 * @static
	 */
	static function EscapeForJavascript($content){
		return EasyReplace($content,array("\\" => "\\\\", "\n" => "\\n","\r" => "\\r","\t" => "\\t","\"" => "\\\"", "<script" => '<scr" + "ipt', "</script>" => '</scr" + "ipt>'));
	}

	/**
	* Sestaveni linku ve smarty helperech.
	*
	* Pozmenuje $params! - maze hodnoty
	*		$params["_connector"]
	*		$params["_anchor"]
	* 	$params["_with_hostname"]
	*		$params["_ssl"]
	*
	* Pri sestavovani linku jsou ignorovany vsechny parametry zacinajici podtrzitkem.
	* Tyto parametry jsou uvazovany jako atributy html tagu.
	*
	* @static
	*/
	static function BuildLink(&$params,&$smarty,$options = array()){
		$options = array_merge(array(
			"connector" => "&",
			"anchor" => null,
			"with_hostname" => false,
			"ssl" => null,
		),$options);
		reset($options);
		while(list($_key,$_value) = each($options)){
			if(isset($params["_$_key"])){
				$options[$_key] = $params["_$_key"];
			}
			unset($params["_$_key"]);
		}

		$_params = $params;

		reset($_params);
		while(list($key,) = each($_params)){
			if(preg_match("/^_/",$key)){ unset($_params[$key]); }
		}

		if(!isset($_params["action"]) && !isset($_params["controller"])){ $_params["action"] = $smarty->_tpl_vars["action"]; }
		if(!isset($_params["controller"])){ $_params["controller"] = $smarty->_tpl_vars["controller"]; }
		if(!isset($_params["action"])){ $_params["action"] = "index"; }
		if(!isset($_params["lang"])){ $_params["lang"] = $smarty->_tpl_vars["lang"]; }

		return Atk14Url::BuildLink($_params,$options);
	}

	/**
	 * Extracts attributes from $params beginning with underscore.
	 *
	 * <code>
	 * $params = array("id" => "20", "_class" => "red", "_id" => "red_link");
	 * $attrs = Atk14Utils::ExtractAttributes($params);
	 *
	 * // or
	 * $attrs = array("data-message" => "Hello guys!");
	 * Atk14Utils::ExtractAttributes($params,$attrs); // the attribute data-message will be preserved
	 * </code>
	 *
	 * $attrs will contain array("class" => "red","id" => "red_link").
	 *
	 * $params will contain array("id" => "20").
	 *
	 * @param array &$params
	 * @param array &$attributes
	 * @return array
	 * @static
	 */
	static function ExtractAttributes(&$params,&$attributes = array()){
		reset($params);
		while(list($_key,$_value) = each($params)){
			if(preg_match("/^_(.+)/",$_key,$matches)){
				$_attr = $matches[1];
				$_attr = str_replace("___","-",$_attr); // this is a hack: "data___type" -> "data-type" (see atk14_smarty_prefilter() function)
				$attributes[$_attr] = $_value;
				unset($params[$_key]);
			}
		}
		return $attributes;
	}

	/**
	 * Joins attributes to a string.
	 *
	 * <code>
	 * $attrs -> array("href" => "http://www.link.cz/", "class" => "red");
	 * $attrs = Atk14Utils::JoinAttributes($attrs);
	 * echo "<a$attrs>text linku</a>"
	 * </code>
	 *
	 * @param array $attributes
	 * @return string joined attributes
	 * @static
	 */
	static function JoinAttributes($attributes){
		reset($attributes);
		$out = array();
		while(list($key,$value) = each($attributes)){
			$out[] = " ".htmlspecialchars($key)."=\"".htmlspecialchars($value)."\"";
		}
		return join("",$out);
	}

	/**
	 * Returns instance of Smarty object.
	 *
	 *
	 * @param string $template_dir
	 * @param array $options
	 * <ul>
	 * <li><b>controller_name</b></li>
	 * </ul>
	 *
	 * @return Smarty instance of Smarty
	 * @static
	 */
	static function GetSmarty($template_dir = null, $options = array()){
		global $ATK14_GLOBAL;

		$options = array_merge(array(
			"controller_name" => ""
		),$options);

		if(function_exists("atk14_get_smarty")){

			$smarty = atk14_get_smarty($template_dir);

		}else{

			$smarty = new Smarty();

			if(!isset($template_dir)){ $template_dir = "./templates/"; }

			if(is_string($template_dir) && !file_exists($template_dir) && file_exists("./templates/$template_dir")){
				$template_dir = "./templates/$template_dir";
			}

			$_template_dir = array();
			if(is_array($template_dir)){
				$_template_dir = $template_dir;
			}else{
				$_template_dir[] = $template_dir;
			}

			$PATH_SMARTY = "/tmp/smarty/";
			if(defined("TEMP")){ $PATH_SMARTY = TEMP."/smarty/"; }
			if(defined("PATH_SMARTY")){ $PATH_SMARTY = PATH_SMARTY; }

			$_template_dir[] = $PATH_SMARTY."/../templates/";

			$smarty->template_dir   =   $_template_dir;
			$smarty->compile_dir    =   $PATH_SMARTY."/templates_c/";
			$smarty->config_dir     =   $PATH_SMARTY."/config/";
			$smarty->cache_dir      =   $PATH_SMARTY."/cache/";
			$smarty->plugins_dir[]  =   $PATH_SMARTY."/plugins/";

			if(!file_exists($smarty->compile_dir)){
				files::mkdir($smarty->compile_dir,$err,$err_str);
			}

			if(!files::is_readable_and_writable($smarty->compile_dir,$err,$err_str)){
				//die("$smarty->compile_dir is not writable!!!");
				// this should by handled by atk14_error_handler()
			}
		}
	
		$namespace = $ATK14_GLOBAL->getValue("namespace");
		
		array_unshift($smarty->plugins_dir,dirname(__FILE__)."/helpers/");
		array_unshift($smarty->plugins_dir,$ATK14_GLOBAL->getApplicationPath()."helpers/");
		array_unshift($smarty->plugins_dir,$ATK14_GLOBAL->getApplicationPath()."helpers/$namespace/");
		array_unshift($smarty->plugins_dir,$ATK14_GLOBAL->getApplicationPath()."helpers/$namespace/$options[controller_name]/");
		$smarty->register_prefilter('atk14_smarty_prefilter');

		return $smarty;
	}

	/**
	 * Writes a message to error log and to the output defined by HTTPResponse
	 *
	 * <code>
	 * Atk14Utils::ErrorLog("chybi sablona _item.tpl",$http_response);
	 * </code>
	 *
	 * @param string $message
	 * @param HTTPResponse
	 * @static
	 */
	static function ErrorLog($message,&$response){
		$message = "AK14 error: $message";
		error_log($message);
		if(defined("DEVELOPMENT") && DEVELOPMENT){
			$response->write($message);
		}else{
			$response->write("AK14 error");
		}
	}

	/**
	 * Tests if controller produced any output.
	 *
	 * Is used for testing in _before_filters
	 *
	 * @param Atk14Controller &$controller
	 * @return boolean true - output produced, false - nothing produced
	 * @static
	 */
	static function ResponseProduced(&$controller){
		return !(
			strlen($controller->response->getLocation())==0 &&
			!$controller->action_executed &&
			$controller->response->buffer->getLength()==0 &&
			$controller->response->getStatusCode()==200
		);
	}

	/**
	 * Joins arrays
	 * <code>
	 * Atk14Utils::JoinArrays(array("a","b"),array("c"),array("d")); // -> array("a","b","c","d")
	 * </code>
	 * @return array joined arrays
	 * @static
	 */
	static function JoinArrays(){
		$out = array();
		$arguments = func_get_args();
		foreach($arguments as $arg){
			if(!isset($arg)){ continue; }
			if(!is_array($arg)){ $arg = array($arg); }
			foreach($arg as $item){
				$out[] = $item;
			}
		}
		return $out;
	}
}

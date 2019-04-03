<?php
/**
 * Class containing several useful methods
 *
 * @filesource
 */

/**
 *
 * Class containing several useful methods
 *
 * @package Atk14\Core
 *
 */
class Atk14Utils{

	/**
	 * Determines environment, also initializes environment when needed.
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
	 * ```
	 * echo Atk14Utils::DetermineEnvironment(); // "PRODUCTION", "DEVELOPMENT" or "TEST"
	 * ```
	 *
	 * @return string
	 */
	static function DetermineEnvironment(){
		global $HTTP_REQUEST;
		// Determining environment constants (i.e. DEVELOPMENT, TEST, PRODUCTION).

		// An existing constant has the strongest importance.
		if(defined("TEST") && TEST){
			defined("DEVELOPMENT") || define("DEVELOPMENT",false);
			defined("PRODUCTION") || define("PRODUCTION",false);
		}elseif(defined("DEVELOPMENT") && DEVELOPMENT){
			defined("TEST") || define("TEST",false);
			defined("PRODUCTION") || define("PRODUCTION",false);	
		}elseif(defined("PRODUCTION") && PRODUCTION){
			defined("DEVELOPMENT") || define("DEVELOPMENT",false);
			defined("TEST") || define("TEST",false);

		// No environment constant was defined? Check out the ATK14_ENV environment variable...
		}elseif(($atk14_env = strtoupper(getenv("ATK14_ENV")))=="TEST"){
			define("TEST",true);
			define("DEVELOPMENT",false);
			define("PRODUCTION",false);
		}elseif($atk14_env=="DEVELOPMENT"){
			define("TEST",false);
			define("DEVELOPMENT",true);
			define("PRODUCTION",false);
		}elseif($atk14_env=="PRODUCTION"){
			define("TEST",false);
			define("DEVELOPMENT",false);
			define("PRODUCTION",true);

		// At last there is an auto detection.
		// If there is an internal remote address or the script is running from a console,
		// environment is treat as DEVELOPMENT.
		}else{
			define("DEVELOPMENT",in_array($HTTP_REQUEST->getRemoteAddr(),array("127.0.0.1","::1")) || php_sapi_name()=="cli");
			define("PRODUCTION",!DEVELOPMENT);
			define("TEST",false);
		}

		$out = "";
		if(DEVELOPMENT){ $out =  "DEVELOPMENT"; }
		if(PRODUCTION){ $out =  "PRODUCTION"; }
		if(TEST){ $out =  "TEST"; }
		return $out;
	}

	/**
	 * Load all config files.
	 *
	 * Loads all config files (*.inc) in directory $ATK14_GLOBAL->getApplicationPath()/../config/
	 * Also tries to use formerly prefered directory $ATK14_GLOBAL->getApplicationPath()/conf
	 *
	 */
	static function LoadConfig(){
		global $ATK14_GLOBAL;
		if(!file_exists($path = $ATK14_GLOBAL->getDocumentRoot()."/config/")){
			$path = $ATK14_GLOBAL->getApplicationPath()."conf/";
		}

		if(file_exists("$path/routers/")){
			class_autoload("$path/routers/");
		}

		$dir = opendir($path);
		while($file = readdir($dir)){
			if(preg_match('/^(local_|)(settings|after_initialize)\.(inc|php)$/',$file)){ continue; } // this is ugly hack :( i need to delay loading of ./config/settings.php na ./config/after_initialize.php
			if(preg_match('/\.(inc|php)$/',$file) && is_file($path.$file)){
				require_once($path.$file);
			}
		}
		closedir($dir);
	}

	/**
	 * Loads resources for a controller and also the controller.
	 *
	 * Load HelpController
	 * ```
	 * Atk14Utils::LoadControllers("help_controller");
	 * ```
	 *
	 * This code loads all resources needed by HelpController and in the end loads the HelpController
	 *
	 * @param string $controller_name name of controller
	 *
	 */
	static function LoadControllers($controller_name){
		global $ATK14_GLOBAL;

		$namespace = $ATK14_GLOBAL->getValue("namespace");

		$_requires = array("$namespace/application.php");
		if($namespace!=""){
			$_requires[] = "$namespace/$namespace.php";
		}
		foreach($_requires as $_f_){
			if($_f_ = atk14_find_file(ATK14_DOCUMENT_ROOT."/app/controllers/$_f_")){
				require_once($_f_);
			}
		}

		Atk14Require::Controller("_*");
		Atk14Require::Controller($controller_name);

		// loading base form class
		foreach(array(
			"$namespace/application_form.php",
			"application_form.php",
		) as $_f_){
			if($_f_ = atk14_find_file(ATK14_DOCUMENT_ROOT."/app/forms/".$_f_)){
				require_once($_f_);
				break;
			}
		}

		// Form:legacy name for base form class
		foreach(array(
			ATK14_DOCUMENT_ROOT."/app/forms/$namespace/form.php",
			ATK14_DOCUMENT_ROOT."/app/forms/form.php",
		) as $_f_){
			if($_f_ = atk14_find_file($_f_)){
				require_once($_f_);
				break;
			}
		}
	}

	/**
	 * Escapes string for use in javascript.
	 *
	 * @param string $content string to be escaped
	 * @return string escaped string
	 */
	static function EscapeForJavascript($content){
		return EasyReplace($content,array("\\" => "\\\\", "\n" => "\\n","\r" => "\\r","\t" => "\\t","\"" => "\\\"", "<script" => '<scr" + "ipt', "</script>" => '</scr" + "ipt>'));
	}

	/**
	 * Build a link for Smarty helpers.
	 *
	 * !Changes $params (clears values)
	 *
	 *		$params["_connector"]
	 *		$params["_anchor"]
	 *		$params["_with_hostname"]
	 *		$params["_ssl"]
	 *
	 * When building a link parameters beginning with underscore are used as parameters of the &lt;a&gt; tag.
	 *
	 *
	 * @param array $params
	 * - action
	 * - controller
	 * - lang
	 * @param Smarty $smarty Smarty specific
	 * @param array $options
	 * - connector - character joining parameters in url
	 * - anchor - 
	 * - with_hostname - boolean - build url even with hostname
	 * - ssl
	 */
	static function BuildLink(&$params,&$smarty,$options = array()){
		$options = array_merge(array(
			"connector" => "&",
			"anchor" => null,
			"with_hostname" => false,
			"ssl" => null,
		),$options);
		foreach($options as $_key => $_value){
			if(isset($params["_$_key"])){
				$options[$_key] = $params["_$_key"];
			}
			unset($params["_$_key"]);
		}

		$_params = $params;

		foreach(array_keys($_params) as $key)	{
			if(preg_match("/^_/",$key)){ unset($_params[$key]); }
		}

		if(!isset($_params["action"]) && !isset($_params["controller"])){ $_params["action"] = $smarty->getTemplateVars("action"); }
		if(!isset($_params["controller"])){ $_params["controller"] = $smarty->getTemplateVars("controller"); }
		if(!isset($_params["action"])){ $_params["action"] = "index"; }
		if(!isset($_params["lang"])){ $_params["lang"] = $smarty->getTemplateVars("lang"); }

		Atk14Utils::_CorrectActionForUrl($params);

		return Atk14Url::BuildLink($_params,$options);
	}

	/**
	 * Extracts attributes from $params beginning with underscore.
	 *
	 * This method is mostly used in helpers to distinguish values that should be rendered as attributes of a tag.
	 *
	 * Recognizes only parameters which names begin with '_'.
	 *
	 * In this example $params will contain array("id" => "20"), $attrs will contain array("class" => "red","id" => "red_link").
	 * ```
	 * $params = array("id" => "20", "_class" => "red", "_id" => "red_link");
	 * $attrs = Atk14Utils::ExtractAttributes($params);
	 * ```
	 *
	 * or
	 * ```
	 * $attrs = array("data-message" => "Hello guys!");
	 * Atk14Utils::ExtractAttributes($params,$attrs);
	 * ```
	 * the attribute data-message will be preserved
	 *
	 *
	 * @param array $params
	 * @param array $attributes
	 * @return array
	 */
	static function ExtractAttributes(&$params,&$attributes = array()){
		foreach($params as $_key => $_value){
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
	 * Example
	 * ```
	 *	$attrs -> array("href" => "http://www.link.cz/", "class" => "red");
	 *	$attrs = Atk14Utils::JoinAttributes($attrs);
	 *	echo "<a$attrs>text linku</a>"
	 * ```
	 *
	 * @param array $attributes
	 * @return string joined attributes
	 */
	static function JoinAttributes($attributes){
		$out = array();
		foreach(array_filter($attributes) as $key => $value){	
			$out[] = " ".h($key)."=\"".h($value)."\"";
		}
		return join("",$out);
	}

	/**
	 * Converts options written in a string into an array
	 *
	 * It may be useful in Smarty modifiers since their options can't be written as array (as in Smarty blocks)
	 *
	 * Usage:
	 * ```
	 *	$options = Atk14Utils::StringToOptions('color=red,with_border,with_decoration=false'); // ["color" => "red", "with_border" => true, "with_decoration" => false]
	 * ```
	 *
	 * Sample usage in a modifier:
	 * ```
	 *	function smarty_modifier_icon($glyph,$options = ""){
	 *		$options = Atk14Utils::StringToOptions($options);
	 *		// ...
	 *	}
	 *
	 *	// usage in a template
	 * 	{"edit"|icon:"color=red,size=20"}
	 * ```
	 *
	 * @param string $options
	 * @return string joined attributes
	 */
	static function StringToOptions($options){
		if(is_array($options)){ return $options; }
		if(trim($options)==""){ return array(); }

		$ar = explode(",",$options);
		$options = array();

		foreach($ar as $item){
			list($key,$value) = strpos($item,'=') ? explode('=',$item) : array($item,true);
			if(strtolower($value)==="true"){
				$value = true;
			}elseif(strtolower($value)==="false"){
				$value = false;
			}
			$options[$key] = $value;
		}

		return $options;
	}

	/**
	 * Returns instance of Smarty object.
	 *
	 *
	 * @param string $template_dir
	 * @param array $options
	 * - <b>controller_name</b>
	 * - namespace
	 * - compile_id_salt
	 *
	 * @return Smarty instance of Smarty
	 */
	static function GetSmarty($template_dir = null, $options = array()){
		global $ATK14_GLOBAL;

		$options = array_merge(array(
			"controller_name" => "",
			"namespace" => "",
			"compile_id_salt" => "",
		),$options);

		$PATH_SMARTY = "/tmp/smarty/";
		if(defined("TEMP")){ $PATH_SMARTY = TEMP."/smarty/"; }
		if(defined("PATH_SMARTY")){ $PATH_SMARTY = PATH_SMARTY; }

		if(!isset($template_dir)){
			$template_dir = ATK14_DOCUMENT_ROOT."/app/views/";
		}

		if(function_exists("atk14_get_smarty")){

			$smarty = atk14_get_smarty($template_dir);

		}else{
			$smarty = new Atk14Smarty();

			if(is_string($template_dir) && !file_exists($template_dir) && file_exists("./templates/$template_dir")){
				$template_dir = "./templates/$template_dir";
			}

			$_template_dir = array();
			if(is_array($template_dir)){
				$_template_dir = $template_dir;
			}else{
				$_template_dir[] = $template_dir;
			}

			$userid = posix_getuid();

			$smarty->setTemplateDir($_template_dir);
			$smarty->setCompileDir($_compile_dir = "$PATH_SMARTY/$userid/templates_c/"); // the uid of the user owning the current process is involved in the compile_dir, thus some file permission issues are solved
			$smarty->setConfigDir($PATH_SMARTY."/config/");
			$smarty->setCacheDir($_cache_dir = "$PATH_SMARTY/$userid/cache/");
			$smarty->setForceCompile(ATK14_SMARTY_FORCE_COMPILE);

			if(!file_exists($_compile_dir)){ Files::Mkdir($_compile_dir); }
			if(!file_exists($_cache_dir)){ Files::Mkdir($_cache_dir); }

			if(!Files::IsReadableAndWritable($_compile_dir)){
				//die("$smarty->compile_dir is not writable!!!");
				// this should by handled by atk14_error_handler()
			}
		}

		if(defined("ATK14_SMARTY_DEFAULT_MODIFIER") && ATK14_SMARTY_DEFAULT_MODIFIER){
			$smarty->default_modifiers[] = ATK14_SMARTY_DEFAULT_MODIFIER;
		}

		// do compile_id zahrneme jmeno controlleru, aby nedochazelo ke kolizim se sablonama z ruznych controlleru, ktere se jmenuji stejne
		$smarty_version_salt = ATK14_USE_SMARTY3 ? "smarty3" : "smarty2";
		$default_modifiers_salt = $smarty->default_modifiers ? "_".md5(serialize($smarty->default_modifiers)) : "";

		$smarty->compile_id = $smarty->compile_id."atk14{$options["compile_id_salt"]}_{$smarty_version_salt}{$default_modifiers_salt}_{$options["namespace"]}_{$options["controller_name"]}_";

		$plugins = $smarty->getPluginsDir();
	
		$smarty->setPluginsDir(array_merge(array(
			$ATK14_GLOBAL->getApplicationPath()."helpers/$options[namespace]/$options[controller_name]/",
			$ATK14_GLOBAL->getApplicationPath()."helpers/$options[namespace]/",
			$ATK14_GLOBAL->getApplicationPath()."helpers/",
			dirname(__FILE__)."/helpers/",
			$PATH_SMARTY."/plugins/",
		),$plugins));

		$smarty->registerFilter('pre','atk14_smarty_prefilter');

		return $smarty;
	}

	/**
	 * Writes a message to error log and to the output defined by HTTPResponse
	 *
	 * Example
	 * ```
	 *	Atk14Utils::ErrorLog("chybi sablona _item.tpl",$http_response);
	 * ```
	 *
	 * @param string $message
	 * @param HTTPResponse $response
	 */
	static function ErrorLog($message,&$response){
		if(!PRODUCTION){
			//$response->write($message);
			throw new Atk14Exception($message);
		}else{
			error_log("AK14 error: $message");
			$response->internalServerError();
		}
	}

	/**
	 * Tests if controller produced any output.
	 *
	 * Is used for testing in _before_filters
	 *
	 * @param Atk14Controller $controller
	 * @return boolean true - output produced, false - nothing produced
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
	 *
	 * Result of this call
	 * ```
	 *	Atk14Utils::JoinArrays(array("a","b"),array("c"),array("d"));
	 * ```
	 * will be array("a","b","c","d")
	 *
	 * @return array joined arrays
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

	/**
	 * Normalizes a URI, removes unnecessary path elements.
	 *
	 * It does not check the existence of individual directories.
	 *
	 * "/path/to/project/atk14/../public/stylesheets/../dist/admin/application.min.js" -> "/path/to/project/public/dist/admin/application.min.js"
	 * "/public/stylesheets/../dist/css/app.css?1384766775" => "/public/dist/css/app.css?1384766775"
	 * ```
	 *	echo Atk14Utils::NormalizeUri('/public/stylesheets/../dist/css/app.css?1384766775');
	 * ```
	 *
	 * @param string $uri uri to normalize
	 * @return string normalized uri
	 */
	static function NormalizeUri($uri){
		$ar = explode('?',$uri);
		$uri = array_shift($ar);

		$uri = preg_replace('#/{2,}#','/',$uri);

		do{
			$orig = $uri;
			$uri = preg_replace('#/[^/]+/\.\./#','/',$uri); // /public/stylesheets/../dist/style.css -> /public/dist/stylesheets.css
		}while($orig!=$uri);

		do{
			$orig = $uri;
			$uri = preg_replace('#/\./#','/',$uri); // /public/./dist/style.css -> /public/dist/stylesheets.css
		}while($orig!=$uri);


		array_unshift($ar,$uri);
		return join('?',$ar);
	}

	/**
	 * Adds HTTP host to the given URI.
	 *
	 * ```
	 * echo Atk14Utils::AddHttpHostToUri("/public/images/logo.png"); // "https://example.com/public/images/logo.png"
	 * ```
	 *
	 * @param string $uri
	 * @return string
	 */
	static function AddHttpHostToUri($uri){
		global $HTTP_REQUEST;

		$proto = $HTTP_REQUEST->ssl() ? "https"	: "http";
		$host = $HTTP_REQUEST->getHttpHost();
		$port = $HTTP_REQUEST->isServerOnStandardPort() ? "" : ":".$HTTP_REQUEST->getServerPort();

		return "$proto://$host$port$uri";
	}

	/**
	 * Normalizes filepath.
	 *
	 * Alias to {@see Atk14Utils::NormalizeUri() NormalizeUri()}
	 *
	 *
	 * @param string $path
	 * @return string normalized path
	 */
	static function NormalizeFilepath($path){
		return Atk14Utils::NormalizeUri($path);
	}

	/**
	 *
	 * An alias for Atk14Locale::Initialize()
	 *
	 * ```
	 * $new_lang = "cs";
	 * $prev_lang = Atk14Utils::InitializeLocale($new_lang);
	 * ```
	 *
	 * @param string new locale
	 * @return string previous locale
	 * @see Atk14Locale::Initialize()
	 */
	static function InitializeLocale(&$lang){
		return Atk14Locale::Initialize($lang);
	}

	/**
	 * Converts the given variable into a scalar
	 *
	 * Casts an exception if conversion fails.
	 *
	 * ```
	 * $book = Book::FindById(5);
	 * echo Atk14Utils::ToScalar($book); // 5
	 *
	 * echo Atk14Utils::ToScalar(123); // 123
	 * echo Atk14Utils::ToScalar("Text"); // "Text"
	 * ```
	 */
	static function ToScalar($var){
		if(is_scalar($var) || is_null($var)){
			return $var;
		}

		if(method_exists($var,"getId")){ return $var->getId(); }
		if(method_exists($var,"toString")){ return $var->toString(); }
		if(method_exists($var,"__toString")){ return $var->__toString(); }

		throw new Exception(sprintf("Can't convert %s var into a scalar value",get_class($var)));
	}

	/**
	 * @ignore
	 */ 
	static function _CorrectActionForUrl(&$params){
		// shortcut to define both controller and action through the action only
		// action="books/detail" -> controller="books", action="detail"
		if(preg_match('/(.+)\/(.+)/',$params["action"],$matches)){
			$params["controller"] = $matches[1];
			$params["action"] = $matches[2];
		}
	}
}

/**
 * Atk14s' variant of require_once
 *
 * When some/path/file.php is given,
 * it loads some/path/file.php or some/path/file.inc
 *
 * @param string $file
 */ 
function atk14_require_once($file){
	($_file = atk14_find_file($file)) || ($_file = $file);
	return require_once($_file);
}

/**
 * When some/path/file.php is given,
 * finds out whether there is some/path/file.php or some/path/file.inc
 *
 * @param string $file
 */
function atk14_find_file($file){
	preg_match('/^(.*\.)(inc|php)$/',$file,$matches);
	$fs = array();
	$fs[] = $file;
	$fs[] = $matches[1]."inc";
	$fs[] = $matches[1]."php";
	foreach($fs as $file){
		if(file_exists($file)){ return $file; }
	}
}

/**
 * Atk14s' way of including required file.
 *
 * @param string $file
 */
function atk14_require_once_if_exists($file){
	if($file = atk14_find_file($file)){
		return atk14_require_once($file);
	}
	return false;
}

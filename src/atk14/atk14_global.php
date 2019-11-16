<?php
/**
 * Class for storing of application globals & settings.
 *
 * @package Atk14
 * @subpackage Core
 * @filesource
 */

/**
 * Class for storing of globals.
 *
 * @package Atk14
 * @subpackage Core
 * @filesource
 *
 */
class Atk14Global{
	/**
	 * Array to store values.
	 *
	 * @var array
	 * @ignore
	 */
	private $_Store = array();

	/**
	 * Static method to get a singleton.
	 *
	 * @return Atk14Global
	 */
	static function &GetInstance(){
		static $instance;
		if(!isset($instance)){
			$instance = new Atk14Global();
		}
		return $instance;
	}

	/**
	 * Stores a value
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	function setValue($name,$value){
		$this->_Store[$name] = $value;
	}

	/**
	 * Gets stored value.
	 *
	 * @param $name name of global variable
	 * @return mixed
	 */
	function getValue($name){
		if(isset($this->_Store[$name])){
			return $this->_Store[$name];
		}
		return null;
	}

	/**
	 * Returns reference to a value.
	 *
	 * Get reference to variable routes
	 * ```
	 * $routes = &$ATK14_GLOBAL->getValue("routes");
	 * $routes = &$ATK14_GLOBAL->getValue("routes",array());
	 * ```
	 *
	 * @param string $name name of global variable
	 * @param string $initial initial value to initialize value when the variable does not exist.
	 * @return reference to global variable
	 */
	function &getValueRef($name,$initial = null){
		if(!isset($this->_Store[$name])){
			$this->_Store[$name] = $initial;
		}
		return $this->_Store[$name];
	}

	/**
	 * Base directory of the application.
	 *
	 * All URLs begin with this value.
	 *
	 * "/", "/eshop/"... 
	 *
	 * @return string application directory
	 */
	function getBaseHref(){
		global $_SERVER;
		$out = "/";
		if(defined("WEB_DOCUMENT_ROOT")){ $out = WEB_DOCUMENT_ROOT; }
		if(defined("WWW_DOCUMENT_ROOT")){ $out = WWW_DOCUMENT_ROOT; }
		if(isset($_SERVER["SCRIPT_NAME"]) && preg_match("/dispatcher.php$/",$_SERVER["SCRIPT_NAME"])){
			$out = preg_replace("/dispatcher.php$/","",$_SERVER["SCRIPT_NAME"]);
		}
		if(defined("ATK14_BASE_HREF")){ $out = ATK14_BASE_HREF; } // the most preffered constant!
		return $out;
	}

	/**
	 * Returns value of HTTP_HOST.
	 *
	 * @return string HTTP_HOST
	 */
	function getHttpHost(){
		global $HTTP_REQUEST;
		$out = "";
		if(isset($HTTP_REQUEST)){ $out = $HTTP_REQUEST->getHttpHost(); }
		if(!$out && defined("ATK14_HTTP_HOST") && ATK14_HTTP_HOST){ $out = ATK14_HTTP_HOST; }
		return $out;
	}

	/**
	 * Returns current application language code.
	 *
	 * If it is not set, returns default 
	 *
	 * @return string
	 */
	function getLang(){
		if(!is_null($lang = $this->getValue("lang"))){
			return $lang;
		}
		return $this->getDefaultLang();	
	}

	/**
	 * Return list of language codes used by the application.
	 *
	 * This call
	 * ```
	 * print_r($ATK14_GLOBAL->getSupportedLangs());
	 * ```
	 * should return
	 * ```
	 * array("en","cs","de");
	 * ```
	 *
	 * @return array set of language codes
	 */
	function getSupportedLangs(){
		if($locales = $this->getConfig("locale")){
			return array_keys($locales);
		}
		return array("cs");
	}

	/**
	 * Returns default language code.
	 *
	 * It's either the ATK14_DEFAULT_LANG constant or the first code in the config/locale.yml.
	 *
	 * @return string "en", "cs"...
	 */
	function getDefaultLang(){
		global $ATK14_GLOBAL;

		if(defined("ATK14_DEFAULT_LANG") && strtolower(ATK14_DEFAULT_LANG)!="auto"){
			return ATK14_DEFAULT_LANG;
		}

		$langs = $this->getSupportedLangs();
		return $langs[0];
	}

	/**
	 * Base directory of the URL with public content.
	 *
	 * "/public/", "/eshop/public/"...
	 *
	 * @return string public directory path
	 */
	function getPublicBaseHref(){
		return $this->getBaseHref()."public/";
	}

	/**
	 * Gets environment.
	 *
	 * There are three types of environment: DEVELOPMENT, TEST, PRODUCTION
	 *
	 * ```
	 * echo $ATK14_GLOBAL->getEnvironment(); // DEVELOPMENT
	 * ```
	 *
	 * @return string name of environment
	 */
	function getEnvironment(){
		return Atk14Utils::DetermineEnvironment();
	}

	/**
	 * Returns database configuration.
	 *
	 * Returns someting like
	 * ```
	 * array(
	 * 	"database" => "dbname",
	 * 	"host" => "127.0.0.1",
	 * 	"username" => "user",
	 * 	"password" => "funny_boy",
	 * 	"port" => "5432",
	 * )
	 * ```
	 *
	 * It searches in `config/database.yml` for the best suited configuration
	 *
	 * ```
	 *	 # configuration_name: "default"
	 *	 development:
	 *	 test:
	 *	 production:
	 *
	 *	 # configuration_name: "cinema"
	 *	 # just one connection for every environment
	 *	 cinema:
	 *
	 *	 # configuration_name: "weather"
	 *	 # a special connection for every environment
	 *	 weather_development:
	 *	 weather_test:
	 *	 weather_production:
	 * ```
	 *
	 * @param string $configuration_name matches one of configurations found in `config/database.yml`
	 * @return array
	 * @todo provide more explanation
	 */
	function getDatabaseConfig($configuration_name = "default"){
		$database_ar = $this->getConfig("database");

		$env = strtolower($this->getEnvironment()); // "development", "test", "production"

		$d = null;

		if($configuration_name=="default" || $configuration_name==""){
			$d = isset($database_ar[$env]) ? $database_ar[$env] : null;
		}elseif(isset($database_ar["{$configuration_name}_$env"])){
			$d = $database_ar["{$configuration_name}_$env"];
		}elseif(isset($database_ar["$configuration_name"])){
			$d = $database_ar["$configuration_name"];
		}

		if(!$d){
			return null;
		}

		$d += array(
			"adapter" => "postgresql",
			"host" => "",
			"port" => "",
			"database" => "",
			"username" => "",
			"password" => "",
		);

		if($d["port"] && !$d["host"]){
			$d["host"] = "localhost";
		}

		if(!$d["port"] && $d["host"]){
			switch($d["adapter"]){
				case "postgresql":
					$d["port"] = "5432";
					break;
				case "mysql":
					$d["port"] = "3306";
					break;
			}
		}
	
		// replacing string values back into the configuration
		//  {{username}}, {{database}}...
		$replaces = array();
		foreach($d as $k => $v){
			if(is_string($v)){
				$replaces['{{'.$k.'}}'] = $v;
			}
		}
		$d = $this->_replace($d,$replaces);

		return $d;
	}

	protected function _replace($value,$replaces){
		if(!$replaces){ return $value; }
		if(is_null($value)){ return null; }
		if(is_array($value)){
			foreach($value as $k => $v){
				$value[$k] = $this->_replace($v,$replaces);
			}
			return $value;
		}
		$value = strtr($value,$replaces);
		return $value;
	}

	/**
	 * Loads and returns configuration from config/$config_name.yml or config/$config_name.json
	 *
	 * If the given config file exists in the directory local_config/ it will be used instead of the one located in the directory config/.
	 *
	 * Returns null when there is no such configuration file
	 *
	 * Example
	 * ```
	 * $ATK14_GLOBAL->getConfig("database");
	 * $ATK14_GLOBAL->getConfig("theme/colors");
	 * $ATK14_GLOBAL->getConfig("theme/colors.json");
	 * ```
	 *
	 * @param string $config_name
	 * @return string|null
	 */
	function getConfig($config_name){
		static $STORE = array();
		if(in_array($config_name,array_keys($STORE))){ return $STORE[$config_name]; }

		$STORE[$config_name] = null;

		// paths in which the configuration file is searched
		$paths = array(
			$this->getDocumentRoot()."/local_config/",
			$this->getDocumentRoot()."/config/",
			$this->getApplicationPath()."conf/", // legacy path, TODO: to be removed
		);

		$suffixes = array("",".yml",".json");

		$filename = "";
		foreach($paths as $path){
			foreach($suffixes as $suffix){
				if(file_exists($_f = "$path$config_name$suffix")){
					$filename = $_f;
					break 2;
				}
			}
		}

		if($filename){
			if(preg_match('/\.yml$/i',$filename)){
				$_config = miniYAML::Load(Files::GetFileContent($filename),array("interpret_php" => true));
			}elseif(preg_match('/\.json$/i',$filename)){
				$_config = json_decode(Files::GetFileContent($filename),true);
			}
			if(is_null($_config)){
				throw new Exception("Atk14Global::getConfig(\"$config_name\"): Unable to load config from $filename");
			}
			$STORE[$config_name] = $_config;
		}

		return $STORE[$config_name];
	}

	/**
	 * @ignore
	 */
	function _getRoot(){
		global $_SERVER;
		$out = "";
		if(defined("DOCUMENT_ROOT")){ $out = DOCUMENT_ROOT; }
		if(defined("APP_DOCUMENT_ROOT")){ $out = APP_DOCUMENT_ROOT; }
		if(isset($_SERVER["SCRIPT_FILENAME"]) && preg_match("/dispatcher.php$/",$_SERVER["SCRIPT_FILENAME"])){
			$out = preg_replace("/dispatcher.php$/","",$_SERVER["SCRIPT_FILENAME"]);
		}
		if(defined("PATH_ATK14_APPLICATION")){
			$out = PATH_ATK14_APPLICATION."/../";
		}
		if(defined("ATK14_DOCUMENT_ROOT")){ $out = ATK14_DOCUMENT_ROOT; } // the most preffered constant!
		if(substr($out,-1)!="/"){ $out .= "/"; }
		return $out;
	}

	/**
	 * Absolute path to the document root
	 *
	 * It surely ends with "/"
	 */
	function getDocumentRoot(){
		return $this->_getRoot();
	}

	/**
	 * Absolute path in filesystem to directory ./public/
	 *
	 * @return string directory path, ie "/var/www/apps/my_atk14_app/public/"
	 *
	 */
	function getPublicRoot(){
		$out = $this->_getRoot()."public/";
		return $out;
	}

	/**
	 * Absolute path in filesystem leading to directory with application.
	 *
	 * This directory is searched for basic application directories (controllers, views, layouts ...)
	 *
	 * @return string application directory path, ie "/var/www/apps/eshop/app/"
	*/
	function getApplicationPath(){
		$out = $this->_getRoot()."app/";
		if(defined("PATH_ATK14_APPLICATION")){ $out = PATH_ATK14_APPLICATION; }
		return $out;
	}

	/**
	 * Absolute path in filesystem leading to directory with migrations scripts.
	 *
	 * This directory is searched for basic application directories (controllers, views, layouts ...)
	 *
	 * @return string application directory path, ie "/var/www/apps/eshop/app/"
	*/
	function getMigrationsPath(){
		return defined("PATH_ATK14_MIGRATIONS") ? PATH_ATK14_MIGRATIONS : $this->_getRoot()."db/migrations/";
	}

	/**
	 * Returns routes description in a form used internally.
	 *
	 * It is possible to filter information for specific lang/controller/action
	 *
	 * ```
	 * $routes = $ATK14_GLOBAL->getPreparedRoutes("",array("path" => "en/product/detail"));
	 * ```
	 *
	 * @param string $namespace
	 * @param array $options possible options:
	 * - lang
	 * - controller
	 * - action
	 * - path
	 * @return array
	 */
	function getPreparedRoutes($namespace = "",$options = array()){
		static $ROUTES_STORE, $ROUTES_BY_PATH, $ROUTES_WITH_NO_PATH;

		$options = array_merge(array(
			"path" => null
		),$options);


		if(!isset($ROUTES_STORE)){
			$ROUTES_STORE = array();
			$ROUTES_BY_PATH = array();
			$ROUTES_WITH_NO_PATH = array();
		}

		if(!isset($ROUTES_STORE[$namespace])){

			$_name = "routes";
			if(strlen($namespace)>0){ $_name .= "[$namespace]"; }
			$routes = $this->getValue("$_name");
			settype($routes,"array");

			// nasleduji 4 pravidla, ktere by mely byt na konci seznamu
			// tyto pravidla zachyti vsechno
			if(!isset($routes[""])){
					$routes[""] = array(
						"lang" => $this->getDefaultLang(),
						"__path__" => "main/index",
						"__page_title__" => "My Application",
						"__page_description__" => "my beautiful application"
					);
			}
			if(!isset($routes["<lang>"])){
					$routes["<lang>"] = array(
						"__path__" => "main/index",
					);
			}			
			if(!isset($routes["<lang>/<controller>"])){
					// pokud action chybi, uvazuje se automaticky "index"
					$routes["<lang>/<controller>"] =  array(
						"action" => "index",
					);
			}	
			if(!isset($routes["<lang>/<controller>/<action>"])){
					$routes["<lang>/<controller>/<action>"] = array();
			}			

			$out = array();

			reset($routes);
			$_last_title = "";
			$_last_description = "";
			foreach($routes as $key => $value){
				// $value["__path__"] = "domain/registration"  -> $value["controller"] = "domain", $value["action"] = "registration"
				if(isset($value["__path__"])){ 
					preg_match("/^(.+)\\/([a-z0-9_]+)$/",$value["__path__"],$matches);
					$value["controller"] = $matches[1];
					$value["action"] = $matches[2];
					unset($value["__path__"]);
				}
				if(!isset($value["__page_title__"])){ $value["__page_title__"] = $_last_title; }
				if(!isset($value["__page_description__"])){ $value["__page_description__"] = $_last_description; }

				$_last_title = $value["__page_title__"];
				$_last_description = $value["__page_description__"];

				if(preg_match_all("/([a-z]{2}):([^\\s]+)/",$key,$matches)){
					for($i=0;$i<sizeof($matches[0]);$i++){
						$lang = $matches[1][$i];
						$url = $matches[2][$i];
						$value["lang"] = $lang;
						$out[$url] = $value;
					}
				}else{
					$out[$key] = $value; 
				}
			}
			$routes = $out;

			// doplneni chybejicich vzoru
			// $routes["domain-registration/<domain_name>"] = array(); -> $routes["domain-registration/<domain_name>"] = array("domain_name" => "/.*/");
			foreach($routes as $key => $value){
				if(preg_match_all("/<([^>]+)>/",$key,$matches)){
					for($i=0;$i<sizeof($matches[0]);$i++){
						$_name = $matches[1][$i];
						if(!isset($value[$_name])){
							if($_name=="controller"){
								$value["controller"] = "/[a-z][a-z_0-9]*/";
							}elseif($_name=="action"){
								$value["action"] = "/[a-z][a-z_0-9]*/";
							}elseif($_name=="lang"){
								$value["lang"] = "/[a-z]{2}/";
							}else{
								$value[$_name] = "/.*/";
							}
						}
					}
				}

				//zde se nastavuje defaultni jazyk
				if(!isset($value["lang"])){ $value["lang"] = $this->getDefaultLang(); }

				$routes[$key] = $value;
			}

			foreach($routes as $uri => $params){
				foreach($params as $name => $val){
					if(preg_match("/^__/",$name)){ continue; }
					$params[$name] = array(
						"regexp" => (bool)preg_match("/^\\/.*\\/$/",$val),
						"value" => $val,
					);
					$routes[$uri] = $params;
				}
			}

			foreach($routes as $uri => $params){
				if(!$params["lang"]["regexp"] && !$params["controller"]["regexp"] && !$params["action"]["regexp"]){
					$ROUTES_BY_PATH[$namespace][$params["lang"]["value"]."/".$params["controller"]["value"]."/".$params["action"]["value"]][$uri] = $params;
				}else{
					$ROUTES_WITH_NO_PATH[$namespace][$uri] = $params;
				}
			}

			$ROUTES_STORE[$namespace] = $routes;
		}

		// pokud se zajimeme o konkretni path,
		// prihodime nakonec i vychozi (nepojmenovane) routy
		if(strlen($path = $options["path"])){
			$out = array();
			if(isset($ROUTES_BY_PATH[$namespace][$path])){ $out = $ROUTES_BY_PATH[$namespace][$path]; }
			foreach($ROUTES_WITH_NO_PATH[$namespace] as $k => $v){
				$out[$k] = $v;
			}
			return $out;
		}
		
		return $ROUTES_STORE[$namespace];
	}

	/**
	 * Returns reference to the global Logger instance
	 *
	 * ```
	 * $logger = $ATK14_GLOBAL->getLogger();
	 * $logger->info("Captain, it's Houston. We have a problem. We have a phone call. It's your wife on the phone.");
	 * $logger->info("Just forget it. Hanging up the phone...");
	 * ```
	 */
	function &getLogger(){
		global $ATK14_LOGGER;

		if(!isset($ATK14_LOGGER)){
			$ATK14_LOGGER = new logger("atk14",array("disable_start_and_stop_marks" => true));
			$ATK14_LOGGER->start();
		}
		return $ATK14_LOGGER;
	}

	/**
	 * Sets the global Logger variable
	 *
	 * @param Logger $logger
	 */
	function setLogger($logger){
		global $ATK14_LOGGER;
		$ATK14_LOGGER = $logger;
	}

	/**
	 * Returns session object
	 *
	 * @return Atk14Session
	 */
	function getSession(){
		return Atk14Session::GetInstance();
	}
}

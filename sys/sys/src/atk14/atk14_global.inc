<?
/**
*	Trida pro ukladani globalnich promennych.
* 
*/
class Atk14Global{
	var $_Store = array();

	/**
	* Staticka metoda pro ziskani jedinacka.
	* 
	* 
	*/
	function &GetInstance(){
		static $instance;
		if(!isset($instance)){
			$instance = new Atk14Global();
		}
		return $instance;
	}

	function setValue($name,$value){
		$this->_Store[$name] = $value;
	}

	function getValue($name){
		if(isset($this->_Store[$name])){
			return $this->_Store[$name];
		}
		return null;
	}

	/**
	* Adresar v url, ve kterem je aplikace umistena.
	* Toto je dulezite pro sestavovani URL.
	* Kazda URL bude timto zacinat.
	*
	* "/", "/application/"... 
	*/
	function getBaseHref(){
		global $_SERVER;
		$out = "/";
		if(defined("WEB_DOCUMENT_ROOT")){ $out = WEB_DOCUMENT_ROOT; }
		if(defined("WWW_DOCUMENT_ROOT")){ $out = WWW_DOCUMENT_ROOT; }
		if(isset($_SERVER["SCRIPT_NAME"]) && preg_match("/dispatcher.php$/",$_SERVER["SCRIPT_NAME"])){
			$out = preg_replace("/dispatcher.php$/","",$_SERVER["SCRIPT_NAME"]);
		}
		if(defined("ATK14_BASE_HREF")){ $out = ATK14_BASE_HREF; }
		return $out;
	}

	/**
	* Adresar v URL, ktery smeruje do ./public/
	*
	* "/public/", "/application/public/"...
	*/
	function getPublicBaseHref(){
		return $this->getBaseHref()."public/";
	}

	function _getRoot(){
		global $_SERVER;
		$out = "";
		if(defined("DOCUMENT_ROOT")){ $out = DOCUMENT_ROOT; }
		if(defined("APP_DOCUMENT_ROOT")){ $out = APP_DOCUMENT_ROOT; }
		if(isset($_SERVER["SCRIPT_FILENAME"]) && preg_match("/dispatcher.php$/",$_SERVER["SCRIPT_FILENAME"])){
			$out = preg_replace("/dispatcher.php$/","",$_SERVER["SCRIPT_FILENAME"]);
		}
		if(defined("ATK14_DOCUMENT_ROOT")){ $out = ATK14_DOCUMENT_ROOT; }
		if(substr($out,-1)!="/"){ $out .= "/"; }
		return $out;
	}

	/**
	* Absolutni cesta ve filesystemu k adresari ./public/
	*
	* @return string 			ie "/var/www/apps/my_atk14_app/public/"
	*/
	function getPublicRoot(){
		$out = $this->_getRoot()."public/";
		return $out;
	}

	/**
	* Absolutni cesta ve filesystemu, ktera smeruje do adresare s aplikaci.
	* V tomto adresi jsou pak hledany podadresare controllers, views, layouts....
	*
	* @return string 			ie "/var/www/apps/eshop/app/"
	*/
	function getApplicationPath(){
		global $_SERVER;
		$out = $this->_getRoot()."app/";
		if(defined("PATH_ATK14_APPLICATION")){ $out = PATH_ATK14_APPLICATION; }
		return $out;
	}

	/**
	* Vrati popis routes v internim rozvinutem formatu.
	* Je mozne filtrovat routes pouze pro dany lang/controller/action:
	* $routes = $ATK14_GLOBAL->getPreparedRoutes("",array("path" => "en/product/detail"));
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
						"lang" => "cs",
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
			while(list($key,$value) = each($routes)){
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
			reset($routes);
			while(list($key,$value) = each($routes)){
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
				if(!isset($value["lang"])){ $value["lang"] = "cs"; }

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

		if(strlen($path = $options["path"])){
			if(!isset($ROUTES_BY_PATH[$namespace][$path])){ return $ROUTES_WITH_NO_PATH[$namespace]; }
			return $ROUTES_BY_PATH[$namespace][$path];
		}
		
		return $ROUTES_STORE[$namespace];
	}
}
?>

<?php
/**
 * Class for working with Urls.
 *
 * Can be used to add a router into application.
 * A router can be used to create nice urls.
 *
 * Add a line into config/routers/load.php
 * ```
 * Atk14Url::AddRouter("ProductsRouter");
 * ```
 * Then create a router class in config/routers/products_router.php
 * ```
 * class ProductsRouter extends Atk14Router {
 * }
 * ```
 *
 * @package Atk14\Core
 * @filesource
 */

/**
 * Class for working with Urls.
 *
 * @package Atk14\Core
 *
 */
class Atk14Url{

	/**
	 * Decodes URI into array of elements.
	 *
	 * When nothing is recognized, returns null.
	 *
	 * ```
	 * $stat = Atk14Url::RecognizeRoute($HTTP_REQUEST->getRequestURI());
	 * ```
	 *
	 * @param string $requested_uri URI to decode
	 * @param array $options
	 * - get_params
	 *
	 * @return array description of URI, contains these parameters:
	 * - controller - name of controller
	 * - action - name of action
	 * - lang - language
	 * - page_title
	 * - page_description
	 * - get_params - associative array of params sent in request
	 * - router - route object that catches the url
	 */
	static function RecognizeRoute($requested_uri,$options = array()){
		global $ATK14_GLOBAL;

		$requested_uri = (string)$requested_uri;
		$options += array(
			"get_params" => null,
		);

		if(is_null($options["get_params"])){
			$options["get_params"] = self::ParseParamsFromUri($requested_uri);
		}

		// /domain-examination/plovarna.cz/?small=1 --> domain-examination/plovarna.cz
		// /domain-examination/plovarna.cz?small=1 --> domain-examination/plovarna.cz
		$uri = $requested_uri;
		$uri = preg_replace('/\?.*/','',$uri);
		$_uri = $uri;
		$uri = preg_replace('/\/$/','',$uri); // odstraneni lomitka na konci
		$trailing_slash = $_uri!=$uri;
		if(strlen($uri)>strlen($ATK14_GLOBAL->getBaseHref())){
			$uri = substr($uri,-(strlen($uri) - strlen($ATK14_GLOBAL->getBaseHref())));
		}else{
			$uri = ""; // prazdne URL
		}

		$namespace = "";
		if(preg_match("/^\\/*([a-z0-9_.-]+)(|\\/.*)$/",$uri,$matches)){
			if(is_dir($ATK14_GLOBAL->getApplicationPath()."controllers/$matches[1]")){
				$namespace = $matches[1];
				$ATK14_GLOBAL->setValue("namespace",$namespace);
				$uri = $matches[2];
				$uri = preg_replace("/^\\//","",$uri);
			}
		}

		$_uri = $uri;
		if($trailing_slash){ $_uri .= "/"; }
		if(!preg_match('/^\//',$_uri)){ $_uri = "/$_uri"; }
		$_params = new Dictionary($options["get_params"]);
		foreach(Atk14Url::GetRouters($namespace) as $router){
			$router->namespace = $namespace;
			$router->params = $_params;
			$router->recognizeUri($_uri,$_params,$namespace);
			if($router->controller && $router->action){
				return Atk14Url::_FindForceRedirect(array(
					"namespace" => $namespace,
					"controller" => $router->controller,
					"action" => $router->action,
					"lang" => $router->lang,
					"page_title" => $router->page_title,
					"page_description" => $router->page_description,
					"get_params" => $router->params->toArray(),
					"force_redirect" => $router->redirected_to,
					"router" => $router
				),$requested_uri);
			}
		}

		$routes = $ATK14_GLOBAL->getPreparedRoutes($namespace);
		//echo "<pre>"; var_dump($routes); echo "<pre>"; 
		$out = null;

		foreach($routes as $pattern => $rules){
			$_replaces = array();
			$_rules = array();
			foreach($rules as $_p_key => $_p_value){	
				if(preg_match("/^__/",$_p_key)){ $_rules[$_p_key] = $_p_value; continue; }
				if($_p_value["regexp"]){
					$_p_value["value"] = substr($_p_value["value"],1,strlen($_p_value["value"])-2); // "/.*/" -> ".*"
					$_replaces["<$_p_key>"] = "(?P<$_p_key>$_p_value[value])";
				}else{
					$_rules[$_p_key] = $_p_value["value"];
				}
			}
			$_pattern = $pattern;
			$_pattern = str_replace("/","\\/",$_pattern);
			$_pattern = strtr($_pattern,$_replaces);
			if(preg_match("/^$_pattern$/",$uri,$matches)){
				foreach($matches as $_key => $_value){
					if(is_int($_key)){ unset($matches[$_key]); continue; }
					$matches[$_key] = urldecode($matches[$_key]); // predpokladame, ze hodnota v REQUEST URI muze byt zakodovana
				}

				$out = array_merge($_rules,$matches);
				break;
			}
		}

		// kontrollery "application" a "atk14" neni mozne zvenku linkovat primo,
		// stejne tak akce "error404", "error403" a "error500" neni mozne linkovat primo.
		if(!isset($out) || in_array($out["controller"],array("application","atk14")) || in_array($out["action"],array("error404","error403","error500"))){
			Atk14Locale::Initialize($out["lang"]);
			return  Atk14Url::_NotFound($namespace);
		}

		$get_params = array();
		foreach($out as $key => $_value){	
			if(in_array($key,array("controller","action","lang","__page_title__","__page_description__","__omit_trailing_slash__"))){ continue; }
			$get_params[$key] = $out[$key];
		}

		$lang_orig = $out["lang"];
		Atk14Locale::Initialize($out["lang"]); // zde muze byt dojit ke zmene $out["lang"]
		if($out["lang"]!=$lang_orig){
			// In the URI there is a language which is not supported by the configuration
			return  Atk14Url::_NotFound($namespace);
		}

		// sestaveni URL s temito parametry, pokud se bude lisit, dojde k presmerovani....
		$get_params = array_merge($options["get_params"],$get_params);

		return Atk14Url::_FindForceRedirect(array(
			"namespace" => $namespace,
			"controller" => $out["controller"],
			"action" => $out["action"],
			"lang" => $out["lang"],

			// protoze jsme page_title a page_description ziskali v def. jazyku (a tedy mozna v jinem nez v prave aktivnim), je tady pouziti funkce _()
			"page_title" => strlen($out["__page_title__"]) ? _($out["__page_title__"]) : "",
			"page_description" => strlen($out["__page_description__"]) ? _($out["__page_description__"]) : "",

			"get_params" => $get_params,
			"force_redirect" => null
		),$requested_uri);
	}

	/**
	 * Checks if a newer uri (using router) should be used.
	 * When yes, force_redirect parameter is filled and a user is redirected to that newer URL.
	 *
	 * @param array $out
	 * @param string $requested_uri
	 */
	static protected function _FindForceRedirect($out,$requested_uri){
		// zde muze byt dojit ke zmene $out["lang"]
		Atk14Locale::Initialize($out["lang"]);

		if($out["force_redirect"]){ return $out; }
		if($out["controller"]=="application" && $out["action"]=="error404"){ return $out; }

		// defined("ATK14_ENABLE_AUTO_REDIRECTING_IN_ADMIN",false); // disables auto redirecting in namespace admin
		$enable_redirecting_by_default = defined("ATK14_ENABLE_AUTO_REDIRECTING") ? ATK14_ENABLE_AUTO_REDIRECTING : true;
		if(
			(!$out["namespace"] && !$enable_redirecting_by_default) ||
			($out["namespace"] && defined($constant_name = "ATK14_ENABLE_AUTO_REDIRECTING_IN_".strtoupper($out["namespace"])) && !constant($constant_name)) ||
			($out["namespace"] && !$enable_redirecting_by_default)
		){
			return $out;
		}

		$expected_link = Atk14Url::BuildLink(array_merge(
			$out["get_params"],
			array(
				"controller" => $out["controller"],
				"action" => $out["action"],
				"lang" => $out["lang"],
				"namespace" => $out["namespace"],
			)
		),array("connector" => "&"));
		if($expected_link!=$requested_uri){
			$out["force_redirect"] = $expected_link;
		}

		return $out;
	}

	static protected function _NotFound($namespace){
		global $ATK14_GLOBAL;
		return array(
			"namespace" => $namespace,
			"controller" => "application",
			"action" => "error404",
			"lang" => $ATK14_GLOBAL->getDefaultLang(),
			"page_title" => "",
			"page_description" => "",
			"get_params" => array(),
			"force_redirect" => null
		);
	}

	/**
	 * Generates a URL
	 *
	 * Option with_hostname specifies whether full url or only path is generated.
	 * When it is set to true configuration constant ATK14_HTTP_HOST will be used as hostname part.
	 * Setting it to a string specific hostname can be used.
	 *
	 * Suppose we have this setup and want to generate url to a product detail
	 * ```
	 * define("ATK14_HTTP_HOST", "www.bestthings.com");
	 * $params = [
	 * 	 "controller" => "products",
	 * 	 "action" => "detail",
	 * 	 "id" => 10,
	 * ];
	 * ```
	 *
	 * ```
	 * echo Atk14Url::BuildLink($params, ["with_hostname" => false, "ssl" => true]); // "/en/products/detail/?id=10"
	 * echo Atk14Url::BuildLink($params, ["with_hostname" => true, "ssl" => true]); // "https://www.bestthings.com/en/products/detail/?id=10"
	 * echo Atk14Url::BuildLink($params, ["with_hostname" => "supergadgets.ie", "ssl" => true]); // "https://supergadgets.ie/en/products/detail/?id=10"
	 *
	 * echo Atk14Url::BuildLink($params, ["basic_auth_string" => "preview", "basic_auth_password" => "s3cr3t"]); // "http://preview:s3cr3t@www.bestthings.com/en/products/detail/?id=10"
	 * ```
	 *
	 *
	 * @param array $params
	 * - **namespace**
	 * - **controller**
	 * - **action**
	 * - **lang**
	 * @param array $options
	 * - **port**
	 * - **ssl**
	 * - **with_hostname** - boolean|string - when true the generated url will contain whole path including hostname and protocol. String specifies specific hostname.
	 * - **anchor**
	 * - **connector**
	 * - ** basic_auth_username **
	 * - ** basic_auth_password **
	 * @return string generated URL
	 *
	 */
	static function BuildLink($params,$options = array(),$__current_ary__ = array()){
		global $ATK14_GLOBAL,$HTTP_REQUEST;

		if(is_string($params)){
			if(preg_match("/^[a-z0-9_]+$/",$params)){
				return Atk14Url::BuildLink(array(
						"action" => $params, 
				),$options,$__current_ary__);
			}
			if(preg_match("/^([a-z0-9_]+)\\/([a-z0-9_]+)$/",$params,$matches)){
				return Atk14Url::BuildLink(array(
						"controller" => $matches[1],
						"action" => $matches[2], 
				),$options,$__current_ary__);
			}

			$url = $params;
			return $url;
		}

		Atk14Timer::Start("Atk14Url::BuildLink");

		$__current_ary__ = array_merge(array(
			"namespace" => (string)$ATK14_GLOBAL->getValue("namespace"), // null -> ""
			"controller" => $ATK14_GLOBAL->getValue("controller"),
			"action" => $ATK14_GLOBAL->getValue("action"),
			"lang" => $ATK14_GLOBAL->getLang(),
		),$__current_ary__);

		if(!isset($params["namespace"])){ $params["namespace"] = $__current_ary__["namespace"]; }
		if(!isset($params["action"]) && !isset($params["controller"])){ $params["action"] = $__current_ary__["action"]; }
		if(!isset($params["controller"])){ $params["controller"] = $__current_ary__["controller"]; }
		if(!isset($params["action"])){ $params["action"] = "index"; }
		if(!isset($params["lang"])){ $params["lang"] = $__current_ary__["lang"]; }

		Atk14Utils::_CorrectActionForUrl($params);

		$options = array_merge(array(
			"connector" => "&",
			"anchor" => null,
			"with_hostname" => false,
			"ssl" => null,
			"port" => null,
			"basic_auth_username" => "",
			"basic_auth_password" => "",
		),$options);

		if(!$options["with_hostname"] && (strlen($options["basic_auth_username"]) || strlen($options["basic_auth_password"]))){
			$options["with_hostname"] = true;
		}
	
		if(is_string($options["with_hostname"])){
			if($options["with_hostname"]=="true"){ $options["with_hostname"] = true;
			}elseif($options["with_hostname"]=="false"){ $options["with_hostname"] = false; }
		}

		if(isset($options["ssl"])){
			$options["with_hostname"] = $options["with_hostname"] ? $options["with_hostname"] : true; // this is correct behaviour - we are expecting it in tests

			if($options["ssl"] && !$HTTP_REQUEST->ssl() && !is_string($options["with_hostname"]) && ATK14_HTTP_HOST!=ATK14_HTTP_HOST_SSL){
				$options["with_hostname"] = ATK14_HTTP_HOST_SSL;
			}
			if(!$options["ssl"] && $HTTP_REQUEST->ssl() && !is_string($options["with_hostname"]) && ATK14_HTTP_HOST!=ATK14_HTTP_HOST_SSL){
				$options["with_hostname"] = ATK14_HTTP_HOST;
			}
		}else{
			$options["ssl"] = $HTTP_REQUEST->ssl();
		}

		if($options["ssl"]){
			if(!$options["port"]){
				$options["port"] = $HTTP_REQUEST->ssl() ? $HTTP_REQUEST->getServerPort() : ATK14_SSL_PORT;
			}
		}else{
			if(!$options["port"]){
				$options["port"] = $HTTP_REQUEST->ssl() ? ATK14_NON_SSL_PORT : $HTTP_REQUEST->getServerPort();
			}
		}

		if(!$options["port"]){
			// ... it's possible that $HTTP_REQUEST->getServerPort() returns null
			$options["port"] = $options["ssl"] ? ATK14_SSL_PORT : ATK14_NON_SSL_PORT;
		}

		$out = null;
		$get_params = array();

		foreach(Atk14Url::GetRouters($params["namespace"]) as $router){
			if($out = $router->buildLink($params)){
				$get_params = $router->params->toArray();
				break;
			}
		}

		if($out){
			$out = preg_replace('/^\//','',$out);
		}else{

			$routes = $ATK14_GLOBAL->getPreparedRoutes($params["namespace"],array("path" => "$params[lang]/$params[controller]/$params[action]"));
			$get_params = array();

			$_params = $params;
			unset($_params["namespace"]);
			unset($_params["controller"]);
			unset($_params["action"]);
			unset($_params["lang"]);

			$out = "";
			foreach($routes as $pattern => $rules){	
				//var_dump($pattern);
				if(!(
					Atk14Url::_ParamMatches($rules["controller"],$params["controller"]) &&
					Atk14Url::_ParamMatches($rules["action"],$params["action"]) &&
					Atk14Url::_ParamMatches($rules["lang"],$params["lang"])
				)){
					continue;
				}

				$_pattern_params = $rules;
				$omit_trailing_slash = isset($rules["__omit_trailing_slash__"]) ? (bool)$rules["__omit_trailing_slash__"] : false;
				unset($_pattern_params["controller"]);
				unset($_pattern_params["action"]);
				unset($_pattern_params["lang"]);
				unset($_pattern_params["__page_title__"]);
				unset($_pattern_params["__page_description__"]);
				unset($_pattern_params["__omit_trailing_slash__"]);

				$_matched = true;
				foreach($_pattern_params as $_p_key => $_p_value){	
					if(!isset($_params[$_p_key])){
						$_matched = false;
						break;
					}
					if(is_object($_params[$_p_key])){ $_params[$_p_key] = $_params[$_p_key]->getId(); }
					if(!Atk14Url::_ParamMatches($_p_value,$_params[$_p_key])){
						$_matched = false;
						break;
					}
				}
				if(!$_matched){ continue; }

				$out = $pattern;
				
				break;
			}

			// nahrazeni <controller>/<action>... -> domain/examination....
			foreach($params as $_key => $_value){	
				if(is_object($_value)){ $_value = (string)$_value->getId(); } // pokud nalezneme objekt, prevedeme jej na string volanim getId()
				if($_key=="namespace"){ continue; } // namespace se umistuje vzdy do URL; neprenasi se v GET parametrech
				if(isset($rules[$_key]["regexp"]) && !preg_match("/^\\/.*\\//",$rules[$_key]["value"])){ continue; }
				if(is_int(strpos($out,"<$_key>"))){
					$out = str_replace("<$_key>",urlencode($_value),$out);
					continue;
				}
				if($_key=="controller" && isset($rules["controller"])){ continue; }
				if($_key=="action" && isset($rules["action"])){ continue; }
				if($_key=="lang" && isset($rules["lang"])){ continue; }
				if(strpos($out,"<$_key>")===false){
					$get_params[$_key] = $_value;
					continue;
				}
			}
			if(strlen($out)>0 && !$omit_trailing_slash){ $out .= "/"; }
		}

		$_namespace = "";
		if(strlen($params["namespace"])>0){ $_namespace = "$params[namespace]/"; }
		$out = $ATK14_GLOBAL->getBaseHref().$_namespace.$out.Atk14Url::EncodeParams($get_params,array("connector" => $options["connector"]));
		if(strlen($options["anchor"])>0){ $out .= "#$options[anchor]"; }

		// Internally, the port 80 is treated as standard ssl port.
		// It's quite common that Apache is running on non-ssl port 80 and ssl is provided by Nginx in reverse proxy mode. 
		$_std_ssl_ports = array(443,80);
		$_std_non_ssl_ports = array(80);

		if($options["with_hostname"]){
			$_server_port = isset($options["port"]) ? $options["port"] : $HTTP_REQUEST->getServerPort();
			$hostname = (is_string($options["with_hostname"])) ? $options["with_hostname"] : $ATK14_GLOBAL->getHttpHost();
			if($HTTP_REQUEST->ssl()){
				$_exp_ports = $_std_ssl_ports;
				$_proto = "https";
			}else{
				$_exp_ports = $_std_non_ssl_ports;
				$_proto = "http";
			}
			$_port = "";
			if($_server_port && !in_array($_server_port,$_exp_ports)){
				$_port = ":".$_server_port;
			}

			if(isset($options["ssl"])){
				if($options["ssl"] && !$HTTP_REQUEST->ssl()){
					$_port = "";
					$_proto = "https";
					if(isset($options["port"]) && !in_array($options["port"],$_std_ssl_ports)){
						$_port = ":$options[port]";
					}
				}
				if(!$options["ssl"] && $HTTP_REQUEST->ssl()){
					$_port = "";
					$_proto = "http";
					if(isset($options["port"]) && !in_array($options["port"],$_std_non_ssl_ports)){
						$_port = ":$options[port]";
					}
				}
			}

			$basic_auth_string = "";
			if(strlen($options["basic_auth_username"]) || strlen($options["basic_auth_password"])){
				$basic_auth_string = $options["basic_auth_username"].":".$options["basic_auth_password"]."@";
			}

			$hostname = "$_proto://$basic_auth_string$hostname$_port";
			$out = $hostname.$out;
		}

		Atk14Timer::Stop("Atk14Url::BuildLink");
		return $out;
	}

	static protected function _EncodeUrlParam($_key,$_value,$options = array()){
		if(is_object($_value)){ $_value = (string)$_value->getId(); } // pokud nalezneme objekt, prevedeme jej na string volanim getId()
		if(is_array($_value)){
			$out = array();
			foreach($_value as $_a_key => $_a_value){
				if(is_numeric($_a_key)) {
					$_a_key = '';
				}
				$out[] = Atk14Url::_EncodeUrlParam($_key."[$_a_key]",$_a_value,$options);
			}
			return join($options["connector"],$out);
		}
		return urlencode($_key)."=".urlencode($_value);
	}

	static function EncodeParams($params, $options = array()){
		$options = array_merge(array(
			"connector" => "&",
		),$options);

		if(is_object($params)){ $params = $params->toArray(); }
		if(!sizeof($params)){ return ""; }

		$out = array();
		foreach($params as $k => $v){
			$out[] = Atk14Url::_EncodeUrlParam($k,$v,$options);
		}

		return "?".join($options["connector"],$out);
	}

	/**
	 *
	 * Adds a router into application.
	 *
	 * ```
	 * Atk14Url::AddRouter(new ProductsRouter());
	 * Atk14Url::AddRouter("ProductsRouter");
	 *
	 * Atk14Url::AddRouter("","ProductsRouter");
	 * Atk14Url::AddRouter("ProductsRouter"); // the same as previous
	 *
	 * Atk14Url::AddRouter("admin","ProductsRouter");
	 * Atk14Url::AddRouter("*","ProductsRouter");
	 * ```
	 * @param string $namespace_or_router
	 * @param string|Atk14Router $router
	 */
	static function AddRouter($namespace_or_router,$router = null){
		if(!isset($router)){
			$router = $namespace_or_router;
			$namespace = "";
			$namespace_defined = false;
		}else{
			$namespace = $namespace_or_router;
			$namespace_defined = true;
		}

		if(is_string($router)){
			$_options = array();
			if($namespace_defined){
				$_options["namespace"] = $namespace;
			}
			$router = new $router($_options); // "ProductsRouter" -> ProductsRouter
		}

		Atk14Url::_SetRouter_GetRouters($namespace,$router);
	}

	/**
	 * Get all routers used in application.
	 *
	 * The list can be narrowed by selecting a namespace.
	 *
	 * @param string $namespace
	 * @return Atk14Router[] array of application routers
	 */
	static function GetRouters($namespace = ""){
		return Atk14Url::_SetRouter_GetRouters($namespace);
	}

	static protected function _SetRouter_GetRouters($namespace,$router = null){
		static $ROUTERS;
		if(!isset($ROUTERS)){ $ROUTERS = array(); }
		if(!isset($ROUTERS["*"])){ $ROUTERS["*"] = array(); }	
		if(!isset($ROUTERS[$namespace])){ $ROUTERS[$namespace] = $ROUTERS["*"]; }

		if(isset($router)){
			if($namespace=="*"){
				foreach($ROUTERS as &$rs){ $rs[] = $router; }
			}else{
				$ROUTERS[$namespace][] = $router;
			}
		}else{
			return $ROUTERS[$namespace];
		}
	}
	
	/**
	 *
	 */
	static protected function _ParamMatches($rule,&$param){
		return
			isset($param) &&
			(
				(!$rule["regexp"] && "$rule[value]"==="$param") ||
				($rule["regexp"] && preg_match($rule["value"],$param))
			);
	}

	/**
	 * Returns parameters from request uri.
	 * ```
	 * $params = Atk14Url::ParseParamsFromUri("/?id=123&format=xml"); // array("id" => "123", "format" => "xml");
	 * ```
	 * @param string $uri
	 * @return array array with parsed parameters.
	 */
	static function ParseParamsFromUri($uri){
		$params = parse_url($uri, PHP_URL_QUERY);
		parse_str( $params, $out );
		return $out;
	}

}

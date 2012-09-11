<?php
/**
 * Class for working with Urls.
 *
 * @package Atk14
 * @subpackage Core
 * @filesource
 */

/**
 * Class for working with Urls.
 *
 * @package Atk14
 * @subpackage Core
 *
 */
class Atk14Url{

	/**
	 * Decodes URI into array of elements.
	 *
	 * When nothing is recognized, returns null.
	 *
	 * <code>
	 * $stat = Atk14Url::RecognizeRoute($HTTP_REQUEST->getRequestURI());
	 * </code>
	 * 
	 * @param string $requested_uri URI to decode
	 *
	 * @return array description of URI, contains these parameters:
	 * <ul>
	 * 	<li><b>controller</b> - name of controller</li>
	 * 	<li><b>action</b> - name of action</li>
	 * 	<li><b>lang</b> - language</li>
	 * 	<li><b>page_title</b> - </li>
	 * 	<li><b>page_description</b> - </li>
	 * 	<li><b>get_params</b> - associative array of params sent in request</li>
	 * </ul>
	 *
	 * @static
	 */
	static function RecognizeRoute($requested_uri){
		global $ATK14_GLOBAL,$_GET;
		settype($uri,"string");

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
		$_params = new Dictionary($_GET);
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
				),$requested_uri);
			}
		}

		$routes = $ATK14_GLOBAL->getPreparedRoutes($namespace);
		//echo "<pre>"; var_dump($routes); echo "<pre>"; 
		$out = null;

		reset($routes);
		while(list($pattern,$rules) = each($routes)){
			$_replaces = array();
			$_rules = array();
			reset($rules);
			while(list($_p_key,$_p_value) = each($rules)){
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
				reset($matches);
				while(list($_key) = each($matches)){
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
			return  Atk14Url::_NotFound($namespace);
		}

		$get_params = array();
		reset($out);
		while(list($key,) = each($out)){
			if(in_array($key,array("controller","action","lang","__page_title__","__page_description__","__omit_trailing_slash__"))){ continue; }
			$get_params[$key] = $out[$key];
		}

		// zde muze byt dojit ke zmene $out["lang"]
		if(function_exists("atk14_initialize_locale")){
			atk14_initialize_locale($out["lang"]);
		}else{
			i18n::init_translation($out["lang"]); // 
		}

		// sestaveni URL s temito parametry, pokud se bude lisit, dojde k presmerovani....
		$get_params = array_merge($_GET,$get_params);

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
	 * Zjisti, zda dane REQUEST URI by nahodou nemelo byt v novejsi variante.
	 * Pokud ano, vyplni se force_redirect a dalsi mechanismy pak zajisti
	 * presmerovani uzivatele na toto nove URL.
	 */
	static function _FindForceRedirect($out,$requested_uri){
		// zde muze byt dojit ke zmene $out["lang"]
		if(function_exists("atk14_initialize_locale")){
			atk14_initialize_locale($out["lang"]);
		}else{
			i18n::init_translation($out["lang"]); // 
		}

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

	static function _NotFound($namespace){
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
	 * @param array $params
	 * <ul>
	 * 	<li><b>namespace</b></li>
	 * 	<li><b>controller</b></li>
	 * 	<li><b>action</b></li>
	 * 	<li><b>lang</b></li>
	 * </ul>
	 * @param array $options
	 * <ul>
	 * 	<li><b>port</b></li>
	 * 	<li><b>ssl</b></li>
	 * 	<li><b>with_hostname</b></li>
	 * 	<li><b>anchor</b></li>
	 * 	<li><b>connector</b></li>
	 * </ul>
	 * @return string generated URL
	 * @static
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
			"namespace" => $ATK14_GLOBAL->getValue("namespace"),
			"controller" => $ATK14_GLOBAL->getValue("controller"),
			"action" => $ATK14_GLOBAL->getValue("action"),
			"lang" => $ATK14_GLOBAL->getValue("lang")
		),$__current_ary__);

		if(!isset($params["namespace"])){ $params["namespace"] = $__current_ary__["namespace"]; }
		if(!isset($params["action"]) && !isset($params["controller"])){ $params["action"] = $__current_ary__["action"]; }
		if(!isset($params["controller"])){ $params["controller"] = $__current_ary__["controller"]; }
		if(!isset($params["action"])){ $params["action"] = "index"; }
		if(!isset($params["lang"])){ $params["lang"] = $__current_ary__["lang"]; }

		$options = array_merge(array(
			"connector" => "&amp;",
			"anchor" => null,
			"with_hostname" => false,
			"ssl" => null,
			"port" => null
		),$options);
	
		if(is_string($options["with_hostname"])){
			if($options["with_hostname"]=="true"){ $options["with_hostname"] = true;
			}elseif($options["with_hostname"]=="false"){ $options["with_hostname"] = false; }
		}

		if(isset($options["ssl"]) && !$options["with_hostname"]){
			$options["with_hostname"] = true;
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
				reset($_pattern_params);
				while(list($_p_key,$_p_value) = each($_pattern_params)){
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
			reset($params);
			while(list($_key,$_value) = each($params)){
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

		if($options["with_hostname"]){
			$_server_port = isset($options["port"]) ? $options["port"] : $HTTP_REQUEST->getServerPort();
			$hostname = (is_string($options["with_hostname"])) ? $options["with_hostname"] : $ATK14_GLOBAL->getHttpHost();
			if($HTTP_REQUEST->sslActive()){
				$_exp_port = 443;
				$_proto = "https";
			}else{
				$_exp_port = 80;
				$_proto = "http";
			}
			$_port = "";
			if($_server_port && $_server_port!=$_exp_port){
				$_port = ":".$_server_port;
			}

			if(isset($options["ssl"])){
				if($options["ssl"] && !$HTTP_REQUEST->sslActive()){
					$_port = "";
					$_proto = "https";
					if(isset($options["port"]) && $options["port"]!=443){
						$_port = ":$options[port]";
					}
				}
				if(!$options["ssl"] && $HTTP_REQUEST->sslActive()){
					$_port = "";
					$_proto = "http";
					if(isset($options["port"]) && $options["port"]!=80){
						$_port = ":$options[port]";
					}
				}
			}

			$hostname = "$_proto://$hostname$_port";
			$out = $hostname.$out;
		}

		Atk14Timer::Stop("Atk14Url::BuildLink");
		return $out;
	}

	static function _EncodeUrlParam($_key,$_value,$options = array()){
		if(is_object($_value)){ $_value = (string)$_value->getId(); } // pokud nalezneme objekt, prevedeme jej na string volanim getId()
		if(is_array($_value)){
			// zatim se tu uvazuje pouze s jednorozmernym indexovanym polem
			// TODO: doplnit hashe a vicerozmerna pole
			$out = array();
			foreach($_value as $_a_key => $_a_value){
				$out[] = Atk14Url::_EncodeUrlParam($_key."[]",$_a_value,$options);
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
	 * Atk14Url::AddRouter(new ProductsRouter());
	 * Atk14Url::AddRouter("ProductsRouter");
	 *
	 * Atk14Url::AddRouter("","ProductsRouter");
	 * Atk14Url::AddRouter("admin","ProductsRouter");
	 * Atk14Url::AddRouter("*","ProductsRouter");
	 */
	static function AddRouter($namespace_or_router,$router = null){
		if(!isset($router)){
			$router = $namespace_or_router;
			$namespace = "";
		}else{
			$namespace = $namespace_or_router;
		}

		if(is_string($router)){ $router = new $router(); } // "ProductsRouter" -> ProductsRouter

		Atk14Url::_SetRouter_GetRouters($namespace,$router);
	}

	/**
	 *
	 */
	static function GetRouters($namespace = ""){
		return Atk14Url::_SetRouter_GetRouters($namespace);
	}

	static function _SetRouter_GetRouters($namespace,$router = null){
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
	 * @access private
	 *
	 */
	static function _ParamMatches($rule,&$param){
		return
			isset($param) &&
			(
				(!$rule["regexp"] && "$rule[value]"==="$param") ||
				($rule["regexp"] && preg_match($rule["value"],$param))
			);
	}

}

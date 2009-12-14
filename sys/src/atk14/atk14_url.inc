<?
class Atk14Url{

	/**
	*
	* $stat = Atk14Url::RecognizeRoute($HTTP_REQUEST->getRequestURI());
	* 
	* $stat["controller"];
	* $stat["action"];
	* $stat["lang"];
	* $stat["page_title"];
	* $stat["page_description"];
	* $stat["get_params"]; // asociativni pole
	* 
	* Pokud nebude URI rozpoznano, bude vracen null
	*
	*/
	function RecognizeRoute($requested_uri){
		global $ATK14_GLOBAL,$_GET;
		settype($uri,"string");

		// /domain-examination/plovarna.cz?small=1 --> domain-examination/plovarna.cz
		$uri = $requested_uri;
		$uri = preg_replace("/\\?.*/","",$uri);
		$uri = preg_replace("/\\/$/","",$uri); // odstraneni lomitka na konci
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

		if(!isset($out)){
			return array(
				"namespace" => $namespace,
				"controller" => "application",
				"action" => "error404",
				"lang" => "cs", // defaultni jazyk
				"page_title" => "",
				"page_description" => "",
				"get_params" => array(),
				"force_redirect" => null
			);
		}

		$get_params = array();
		reset($out);
		while(list($key,) = each($out)){
			if(in_array($key,array("controller","action","lang","__page_title__","__page_description__"))){ continue; }
			$get_params[$key] = $out[$key];
		}

		// zde muze byt dojit ke zmene $out["lang"]
		if(function_exists("atk14_initialize_locale")){
			atk14_initialize_locale($out["lang"]);
		}else{
			i18n::init_translation($out["lang"]); // 
		}

		// sestaveni URL s temito parametry, pokud se bude lisit, dojde k presmerovani....
		$_get_params = array_merge($_GET,$get_params);
		$expected_link = Atk14Url::BuildLink(array_merge(
			$_get_params,
			array(
				"controller" => $out["controller"],
				"action" => $out["action"],
				"lang" => $out["lang"],
			)
		),array("connector" => "&"));
		$_force_redirect = null;
		if($expected_link!=$requested_uri){
			$_force_redirect = $expected_link;
		}

		return array(
			"namespace" => $namespace,
			"controller" => $out["controller"],
			"action" => $out["action"],
			"lang" => $out["lang"],
			"page_title" => $out["__page_title__"],
			"page_description" => $out["__page_description__"],
			"get_params" => $get_params,
			"force_redirect" => $_force_redirect
		);
	}

	function BuildLink($params,$options = array()){
		global $ATK14_GLOBAL,$HTTP_REQUEST;
		Atk14Timer::Start("Atk14Url::BuildLink");
		$params = array_merge(array(
			"namespace" => $ATK14_GLOBAL->getValue("namespace"),
			"controller" => $ATK14_GLOBAL->getValue("controller"),
			"action" => $ATK14_GLOBAL->getValue("action"),
			"lang" => $ATK14_GLOBAL->getValue("lang")
		),$params);
		//echo "<pre>"; var_dump($params); echo "</pre>";
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

		$routes = $ATK14_GLOBAL->getPreparedRoutes($params["namespace"],array("path" => "$params[lang]/$params[controller]/$params[action]"));
		$get_params = array();

		$_params = $params;
		unset($_params["namespace"]);
		unset($_params["controller"]);
		unset($_params["action"]);
		unset($_params["lang"]);

		$out = "";
		reset($routes);
		while(list($pattern,$rules) = each($routes)){
			if(!(
				Atk14Url::_ParamMatches($rules["controller"],$params["controller"]) &&
				Atk14Url::_ParamMatches($rules["action"],$params["action"]) &&
				Atk14Url::_ParamMatches($rules["lang"],$params["lang"])
			)){
				continue;
			}

			$_pattern_params = $rules;
			unset($_pattern_params["controller"]);
			unset($_pattern_params["action"]);
			unset($_pattern_params["lang"]);
			unset($_pattern_params["__page_title__"]);
			unset($_pattern_params["__page_description__"]);

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
				if(is_array($_value)){
					// zatim se tu uvazuje pouze s jednorozmernym indexovanym polem
					// TODO: doplnit hashe a vicerozmerna pole
					foreach($_value as $_a_key => $_a_value){
						$get_params[] = urlencode($_key."[]")."=".urlencode($_a_value);
					}
					continue;
				}
				$get_params[] = urlencode($_key)."=".urlencode($_value);
				continue;
			}
		}

		$_lomitko = "";
		if(strlen($out)>0){ $_lomitko = "/"; }
		$_namespace = "";
		if(strlen($params["namespace"])>0){ $_namespace = "$params[namespace]/"; }
		$out = $ATK14_GLOBAL->getBaseHref().$_namespace.$out.$_lomitko;
		if(sizeof($get_params)>0){ $out .= "?".join($options["connector"],$get_params); }
		if(strlen($options["anchor"])>0){ $out .= "#$options[anchor]"; }

		if($options["with_hostname"]){
			$_server_port = isset($options["port"]) ? $options["port"] : $HTTP_REQUEST->getServerPort();
			$hostname = (is_string($options["with_hostname"])) ? $options["with_hostname"] : $HTTP_REQUEST->getHTTPHost();
			if($HTTP_REQUEST->sslActive()){
				$_exp_port = 443;
				$_proto = "https";
			}else{
				$_exp_port = 80;
				$_proto = "http";
			}
			$_port = "";
			if($_server_port!=$_exp_port){
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

	function _ParamMatches($rule,&$param){
		return
			isset($param) &&
			(
				(!$rule["regexp"] && "$rule[value]"==="$param") ||
				($rule["regexp"] && preg_match($rule["value"],$param))
			);
	}

}
?>

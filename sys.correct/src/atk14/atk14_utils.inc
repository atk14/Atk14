<?
class Atk14Utils{
	/**
	* Nahraje vsechny $ATK14_GLOBAL->getApplicationPath()/../config/*.inc soubory.
	* Drive preferovane umisteni $ATK14_GLOBAL->getApplicationPath()/conf/*.inc.
	*/
	function LoadConfig(){
		global $ATK14_GLOBAL;
		if(!file_exists($path = $ATK14_GLOBAL->getApplicationPath()."../config/")){
			$path = $ATK14_GLOBAL->getApplicationPath()."conf/";
		}
		$dir = opendir($path);
		while($file = readdir($dir)){
			if(preg_match("/.inc$/",$file) && is_file($path.$file)){
				require_once($path.$file);
				//require_once($ATK14_GLOBAL->getApplicationPath()."conf/$file");
			}
		}
		closedir($dir);

		if(file_exists($_f = $path."database.yml")){
			$ATK14_GLOBAL->setValue("database",miniYAML::Load(files::get_file_content($_f,$e,$e_s)));
		}

		if(file_exists($_f = $path."locale.yml")){
			$ATK14_GLOBAL->setValue("locale",miniYAML::Load(files::get_file_content($_f,$e,$e_s)));
		}
	}

	/**
	* Atk14Utils::LoadControllers("help_controller");
	* Nahraje vse potrebne pro HelpController a nakonec nahraje i HelpController samotny.
	*/
	function LoadControllers($controller_name){
		Atk14Require::Controller("ApplicationController");
		Atk14Require::Controller("_*");
		Atk14Require::Controller($controller_name);
	}

	/**
	* Escapne string pro pouziti v javascriptu.
	*/
	function EscapeForJavascript($content){
		return EasyReplace($content,array("\\" => "\\\\", "\n" => "\\n","\r" => "\\r","\"" => "\\\""));
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
	*/
	function BuildLink(&$params,&$smarty,$options = array()){
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
	* Vytahne z $params vsechny hodnoty, jimz klic zacina podrtzitkem.
	* 
	* $params = array("id" => "20", "_class" => "red", "_id" => "red_link");
	* $attrs = Atk14Utils::ExtractAttributes($params);
	* 
	*	$attrs --> array("class" => "red","id" => "red_link")
	* $params --> array("id" => "20")
	*
	*/
	function ExtractAttributes(&$params){
		$attributes = array();
		reset($params);
		while(list($_key,$_value) = each($params)){
			if(preg_match("/^_(.+)/",$_key,$matches)){
				$attributes[$matches[1]] = $_value;
				unset($params[$_key]);
			}
		}
		return $attributes;
	}

	/**
	*	Spoji atributy do stringu
	*
	* $attrs -> array("href" => "http://www.link.cz/", "class" => "red");
	*
	* $attrs = Atk14Utils::JoinAttributes($attrs);
	* echo "<a$attrs>text linku</a>"
	*/
	function JoinAttributes($attributes){
		reset($attributes);
		$out = array();
		while(list($key,$value) = each($attributes)){
			$out[] = " ".htmlspecialchars($key)."=\"".htmlspecialchars($value)."\"";
		}
		return join("",$out);
	}

	function GetSmarty($template_dir = null, $options = array()){
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
				die("$smarty->compile_dir is not writable!!!");
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
	* Atk14Utils::ErrorLog("chybi sablona _item.tpl",$http_response);
	*/
	function ErrorLog($message,&$response){
		$message = "AK14 error: $message";
		error_log($message);
		if(defined("DEVELOPMENT") && DEVELOPMENT){
			$response->write($message);
		}else{
			$response->write("AK14 error");
		}
	}

	/**
	* Vyprodukoval tento kontroler nejaky vystup?
	* Pouziva se pro testovani v before filtrech.
	*/
	function ResponseProduced(&$controller){
		return !(
			strlen($controller->response->getLocation())==0 &&
			!$controller->action_executed &&
			$controller->response->buffer->getLength()==0 &&
			$controller->response->getStatusCode()==200
		);
	}

	/**
	* Atk14Utils::JoinArrays(array("a","b"),array("c"),array("d")); // -> array("a","b","c","d")
	*/
	function JoinArrays(){
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
?>

<?php
/**
 * Class for simple including of files.
 *
 * @package Atk14\Core
 * @filesource
 */

/**
 * Class for simple including of files.
 *
 */
class Atk14Require{
	/**
	 * Loads form files by pattern.
	 *
	 * It automatically uses current namespace.
	 *
	 * This example loads all forms for controller article:
	 * ```
	 * Atk14Require::Forms("article/*");
	 * ```
	 *
	 * @param string $pattern pattern specifying filename
	 * @return string[] array with filenames of loaded forms
	 */
	static function Forms($pattern){
		global $ATK14_GLOBAL;
		return Atk14Require::_Files("forms/".$ATK14_GLOBAL->getValue("namespace")."/$pattern");
	}

	/**
	 * Loads a controller.
	 *
	 * Loads a controller by name. The controller name can be specified by several ways:
	 *
	 * Namespace doesn't have to be specified, it is used automatically.
	 * - classname
	 * ```
	 * Atk14Require::Controller("ApplicationController");
	 * Atk14Require::Controller("HelpController");
	 * ```
	 *
	 * - filename. You don't have to specify suffix, it will be added automatically.
	 * ```
	 * Atk14Require::Controller("help_controller");
	 * Atk14Require::Controller("help_controller.php");
	 * ```
	 *
	 * - other
	 * ```
	 * Atk14Require::Controller("_*");
	 * ```
	 *
	 * @param string $controller_name 
	 * @return string[]
	 */
	static function Controller($controller_name){
		global $ATK14_GLOBAL;
		$filename = "";
		$namespace = $ATK14_GLOBAL->getValue("namespace");

		if($controller_name == "ApplicationController"){
			$filename = "application.*";
		}elseif(preg_match("/^([A-Z].*)Controller$/",$controller_name,$matches)){
			$filename = strtolower($matches[1])."_controller.*";
		}elseif(preg_match("/_controller/",$controller_name)){
			$filename = "$controller_name.*";
		}

		$pattern = "controllers/$namespace/";
		if($filename!=""){
			$pattern .= "$filename";
		}else{
			$pattern .= "$controller_name";
		}
		return Atk14Require::_Files($pattern);
	}

	/**
	 * Loads a helper
	 *
	 * Loading a helper examples
	 * ```
	 * Atk14Require::Helper("function.paginator");
	 * Atk14Require::Helper("block.message");
	 * Atk14Require::Helper("modifier.format_datetime",$smarty);
	 * Atk14Require::Helper("modifier.format_datetime.php",$smarty);
	 * ```
	 *
	 * @param string $filename name of helper. extension doesn't have to be specified, it will be added automatically
	 * @param Atk14Smarty $smarty
	 * @return string[]
	 */
	static function Helper($filename,$smarty = null){
		!preg_match('/\.php$/',$filename) && ($filename .= ".php");

		$function = String4::ToObject($filename)->gsub('/\.php$/','')->replace('.','_')->prepend('smarty_')->toString(); // "block.message.php" -> "smarty_block_message"
		if(function_exists($function)){
			return array();
		}

		if(!$smarty){ $smarty = Atk14Utils::GetSmarty(); }
		$plugins_dir = $smarty->getPluginsDir();
		foreach($plugins_dir as $dir){
			if(file_exists("$dir/$filename")){
				require_once("$dir/$filename");
				return array("$dir/$filename");
			}
		}
		return array();
	}

	/**
	 * Loads filename specified by pattern.
	 *
	 * Alias of {@link _Files()} method.
	 * ```
	 * Atk14Require::Load("controllers/application_mailer.inc");
	 * Atk14Require::Load("controllers/*.inc");
	 * ```
	 *
	 * @param string $pattern
	 * @return string[]
	 */
	static function Load($pattern){
		return Atk14Require::_Files($pattern);
	}

	/**
	 *
	 * Loads files specified by pattern.
	 *
	 * @ignore
	 *
	 */
	static function _Files($pattern){
		global $ATK14_GLOBAL;

		$out = array();

		if(!$ar = glob($ATK14_GLOBAL->getApplicationPath()."$pattern")){ return $out; }

		foreach($ar as $filename){
			$basename = basename($filename);
			if(!preg_match("/^[a-z0-9_].*\\.(inc|php)$/",$basename)){ continue; }
			$out[] = $filename;
			atk14_require_once($filename);
		}

		return $out;
	}

}	

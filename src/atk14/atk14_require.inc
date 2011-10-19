<?php
/**
 * Class for simple including of files.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Class for simple including of files.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 *
 */
class Atk14Require{
	/**
	 * Loads forms by pattern.
	 *
	 * It automatically uses current namespace.
	 *
	 * This example loads all forms for controller article:
	 * <code>
	 * Atk14Require::Forms("article/*");
	 * </code>
	 * @param string $pattern
	 * @return array
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
	 * <ul>
	 * 	<li>classname
	 * 	<code>
	 * 	Atk14Require::Controller("ApplicationController");
	 * 	Atk14Require::Controller("HelpController");
	 * 	</code>
	 * 	</li>
	 * 	<li>filename. You don't have to specify suffix, it will be added automatically.
	 * 	<code>
	 * 	Atk14Require::Controller("help_controller");
	 * 	Atk14Require::Controller("help_controller.inc");
	 * 	</code>
	 * 	</li>
	 * 	<li>other
	 * 	<code>
	 * 	Atk14Require::Controller("_*");
	 * 	</code>
	 * 	</li>
	 * </ul>
	 *
	 * @param string $controller_name 
	 * @return array
	 */
	static function Controller($controller_name){
		global $ATK14_GLOBAL;
		$filename = "";
		if($controller_name == "ApplicationController"){
			$filename = "application.inc";
		}elseif(preg_match("/^([A-Z].*)Controller$/",$controller_name,$matches)){
			$filename = strtolower($matches[1])."_controller.inc";
		}elseif(preg_match("/_controller/",$controller_name)){
			$filename = "$controller_name.inc";
		}

		$pattern = "controllers/".$ATK14_GLOBAL->getValue("namespace")."/";
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
	 * <code>
	 * Atk14Require::Helper("modifier.format_datetime",$smarty);
	 * Atk14Require::Helper("modifier.format_datetime.php",$smarty);
	 * </code>
	 *
	 * @param string $filename name of helper. extension doesn't have to be specified, it will be added automatically
	 * @return array
	 */
	static function Helper($filename,&$smarty){
		!preg_match("/\\.php$/",$filename) && ($filename .= ".php");
		$plugins_dir = $smarty->plugins_dir;
		foreach($plugins_dir as $dir){
			if(file_exists("$dir/$filename")){
				require_once("$dir/$filename"); return array("$dir/$filename");
			}
		}
		return array();
	}

	/**
	 * Loads filename specified by pattern.
	 *
	 * Alias of {@link _Files()} method.
	 * <code>
	 * Atk14Require::Load("controllers/application_mailer.inc");
	 * Atk14Require::Load("controllers/*.inc");
	 * </code>
	 *
	 * @param string $pattern
	 * @return array
	 * @uses _Files()
	 * @static
	 */
	static function Load($pattern){
		return Atk14Require::_Files($pattern);
	}

	/**
	 *
	 * Loads files specified by pattern.
	 *
	 * @static
	 *
	 */
	static function _Files($pattern){
		global $ATK14_GLOBAL;

		$out = array();

		if(!$ar = glob($ATK14_GLOBAL->getApplicationPath()."$pattern")){ return $out; }

		reset($ar);
		while(list(,$filename) = each($ar)){
			$basename = basename($filename);
			if(!preg_match("/^[a-z0-9_].*\\.(inc|php)$/",$basename)){ continue; }
			$out[] = $filename;
			require_once($filename);
		}

		return $out;
	}

}	
?>

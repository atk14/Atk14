<?
/**
* Trida pro snadne includovani souboru.
*

*/
class Atk14Require{
	/**
	* // nacteni vsech formularu pro kontroler article
	* // automaticky bude doplnen namespace, takze napr. tak mohou byt natazeny vsechny soubory z forms/admin/article/
	* Atk14Require::Forms("article/*");
	*/
	function Forms($pattern){
		global $ATK14_GLOBAL;
		return Atk14Require::_Files("forms/".$ATK14_GLOBAL->getValue("namespace")."/$pattern");
	}

	/**
	* Do parametru neuvadet namespace - ten bude pridan automaticky.
	* 
	* Atk14Require::Controller("ApplicationController");
	* Atk14Require::Controller("HelpController");
	* Atk14Require::Controller("help_controller");
	* Atk14Require::Controller("help_controller.inc");
	* Atk14Require::Controller("_*");
	*/
	function Controller($controller_name){
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
	* Nahraje helper.
	*
	* Atk14Require::Helper("modifier.format_datetime",$smarty);
	* Atk14Require::Helper("modifier.format_datetime.php",$smarty);
	*/
	function Helper($filename,&$smarty){
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
	* Atk14Require::Load("controllers/application_mailer.inc");
	*/
	function Load($pattern){
		return Atk14Require::_Files($pattern);
	}

	function _Files($pattern){
		global $ATK14_GLOBAL;

		$out = array();

		$ar = glob($ATK14_GLOBAL->getApplicationPath()."$pattern");


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

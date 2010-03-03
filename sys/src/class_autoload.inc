<?
/**
* class_autoload(dirname(__FILE__));
*
* // nebo
*
* class_autoload(array(
* 	"ClassName" => dirname(__FILE__)."/class_name.inc",
* 	"AnotherClassName" => dirname(__FILE__)."/another_class_name_2.inc",
* ));
*/
function class_autoload($params){
	if(is_string($params)){
		__class_autoload__(array("directory" => $params));
		return;
	}
	if(is_array($params)){
		__class_autoload__(array("filenames_by_class" => $params));
		return;
	}
	
}

function __class_autoload__($options_or_class_name){
	static $store_filenames, $directories, $spl_autoload_registered;
	if(!isset($store_filenames)){ $store_filenames = array(); }
	if(!isset($directories)){ $directories = array(); }
	if(!isset($spl_autoload_registered)){ $spl_autoload_registered = false; }


	if(is_string($options_or_class_name)){
		$class_name = $options_or_class_name;
		if(isset($store_filenames[strtolower($class_name)])){ require_once($store_filenames[strtolower($class_name)]); }
		foreach($directories as $d){
			if(file_exists("$d/".strtolower($class_name).".inc")){ require_once("$d/".strtolower($class_name).".inc"); }
			if(file_exists("$d/$class_name.inc")){ require_once("$d/$class_name.inc"); }

			if(file_exists("$d/".strtolower($class_name).".php")){ require_once("$d/".strtolower($class_name).".php"); }
			if(file_exists("$d/$class_name.php")){ require_once("$d/$class_name.php"); }
		}

		return;
	}

	$options = array_merge(array(
		"filenames_by_class" => array(),
		"directory" => null,
	),$options_or_class_name);
	
	foreach($options["filenames_by_class"] as $c_name => $f_name){
		$store_filenames[strtolower($c_name)] = $f_name;
	}
	if(isset($options["directory"])){ $directories[] = $options["directory"]; }

	if(!$spl_autoload_registered){
		spl_autoload_register("__class_autoload__");
		$spl_autoload_registered = true;
	}
}

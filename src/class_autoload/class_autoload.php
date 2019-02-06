<?php
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
	static $entries_count = 0;
	if($entries_count>0){ return; }
	$entries_count++;
	if(is_string($params)){
		__class_autoload__(array("directory" => $params));
	}elseif(is_array($params)){
		__class_autoload__(array("filenames_by_class" => $params));
	}
	$entries_count--;
}

function __class_autoload__($options_or_class_name){
	static $store_filenames, $directories, $spl_autoload_registered, $already_checked_classes;

	if(!isset($store_filenames)){
		$store_filenames = array();
		$directories = array();
		$spl_autoload_registered = false;
		$already_checked_classes = array();
	}

	if(is_string($options_or_class_name)){
		$class_name = $options_or_class_name;

		$_key = strtolower($class_name);
		if(in_array($_key,$already_checked_classes)){ return; }
		$already_checked_classes[] = $_key;


		if(isset($store_filenames[$_key])){ require_once($store_filenames[strtolower($class_name)]); }
		# namespace to directory Namespace\ClassName -> Namespace/ClassName
		$class_name = str_replace("\\", '/', $class_name);
		$_filenames = array(
			$class_name,
			preg_replace('/([a-z0-9])([A-Z])/','\1_\2',$class_name), // RedFruit -> red_fruit; Ean13NumberField -> ean13_number_field
		);

		$filenames = array();
		foreach($_filenames as $f){
			if(in_array("$f.inc",$filenames)){ continue; }
			$filenames[] = "$f.inc";
			$filenames[] = "$f.php";
			if(strtolower($f)!=$f){
				$filenames[] = strtolower($f).".inc";
				$filenames[] = strtolower($f).".php";
			}
		}

		foreach($directories as $d){
			foreach($filenames as $f){
				if(file_exists("$d/$f")){ require_once("$d/$f"); }
				if(class_exists($class_name)){ return true; }
			}
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
	if(isset($options["directory"])){
		$directories[] = $options["directory"];
		$already_checked_classes = array();
	}

	if(!$spl_autoload_registered){
		spl_autoload_register("__class_autoload__");
		$spl_autoload_registered = true;
	}
}

<?php
class Atk14Fixture {

	protected static $loaded_fixtures = array();

	static function ClearLoadedFixtures(){
		self::$loaded_fixtures = array();
	}

	/**
	 * Loads given fixture
	 *
	 * Usage:
	 *
	 *	$list = Atk14Fixture::Load("galleries");
	 *
	 * This loads fixtures according to a file tests/fixtures/galleries.yml.
	 * It throws an exception when the file doesn't exist or its content is corrupted.
	 *
	 * @return Atk14FixtureList
	 */
	static function Load($name,$options = array()){
		global $ATK14_GLOBAL;

		$options += array(
			"class_name" => null,
			"dbmole" => $GLOBALS["dbmole"],

			"reload_fixture" => false,
		);

		if(!$options["reload_fixture"] && isset(self::$loaded_fixtures[$name])){
			return self::$loaded_fixtures[$name];
		}

		$dbmole = $options["dbmole"];
	
		$dir = $ATK14_GLOBAL->getApplicationPath()."/../test/fixtures/";
		$filename = "$dir/$name.yml";

		if (!file_exists($filename)) {
			throw new Exception("Fixture $name not found ($filename)");
		}

		$content = Files::GetFilecontent($filename);

		// Getting all fixtures from the fixtures directory
		$all_fixtures = array();
		foreach(Files::FindFiles($dir,array("maxdepth" => 1, "pattern" => '/^[a-z0-9_]+\.yml/')) as $_f){
			$all_fixtures[] = preg_replace('/^.*\/([^\/]+)\.yml/','\1',$_f);
		}

		// Getting all fixtures which are used in the requested fixture
		// I know that the method may not be totally accurate but it's okay to load a bit more fixtures then is actually needed.
		preg_match_all('/\$('.join('|',$all_fixtures).'\b)/',$content,$matches);
		$required_fixtures = $matches[1] ? array_combine($matches[1],$matches[1]) : array(); // PHP5.3 Warning:  array_combine(): Both parameters should have at least 1 element
		unset($required_fixtures[$name]);
		$required_fixtures = array_values($required_fixtures);

		// Auto-loading of all used fixtures
		foreach($required_fixtures as $required_fixture){
			Atk14Fixture::Load($required_fixture);
		}

		if(!$options["reload_fixture"] && isset(self::$loaded_fixtures[$name])){
			return self::$loaded_fixtures[$name];
		}

		$class_name = $options["class_name"];
		if(is_null($class_name)){
			// In the fixture YAML file there can be class set this way:
			//
			// # class_name: Article
			if(preg_match('/^(.*\n|)# *class_name: +"?(?P<class_name>[a-zA-Z0-9_]+)"?\s*(\n.*|)$/s',$content,$matches)){
				$class_name = $matches["class_name"];
			}
		}

		$table_name = "";
		if(preg_match('/^(.*\n|)# *table_name: +"?(?P<table_name>[a-zA-Z0-9_]+)"?\s*(\n.*|)$/s',$content,$matches)){
			// In the fixture YAML file there can be table_name set this way:
			//
			// # table_name: article_images
			$table_name = $matches["table_name"];
		}

		if(strlen($class_name)==0 && strlen($table_name)==0){
			$class_name = String4::ToObject($name)->singularize()->camelize()->toString(); // "gallery_items" -> "GalleryItem"
			if(!class_exists($class_name) || !method_exists($class_name,'CreateNewRecord')){
				$class_name = "";
			}
		}

		$data = miniYAML::Load(Files::GetFileContent($filename),array(
			"interpret_php" => true,
			"values" => &self::$loaded_fixtures, // ["products" => [...], ""]
		));
		if(!$data){
			throw new Exception("Parsing YAML failed in $filename");
		}

		$list = new Atk14FixtureList($class_name);
		foreach($data as $k => $values) {
			if($class_name){
				$o = $class_name::CreateNewRecord($values);
			}elseif($table_name){
				$dbmole->insertIntoTable($table_name,$values);
				$o = $values;
			}else{
				// default
				$dbmole->insertIntoTable($name,$values);
				$o = $values;
			}
			//$list[$k] = $o->getId();
			$list[$k] = $o;
		}

		self::$loaded_fixtures[$name] = $list;

		return $list;
	}
}

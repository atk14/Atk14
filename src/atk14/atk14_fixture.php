<?php
class Atk14Fixture {

	protected static $loaded_fixtures = array();

	static function ClearLoadedFixtures(){
		self::$loaded_fixtures = array();
	}

	/**
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

			"reload_fixture" => true,
		);

		if(!$options["reload_fixture"] && isset(self::$loaded_fixtures[$name])){
			return self::$loaded_fixtures[$name];
		}

		$dbmole = $options["dbmole"];

		$filename = sprintf("%s/../test/fixtures/%s.yml", $ATK14_GLOBAL->getApplicationPath(), $name);
		if (!file_exists($filename)) {
			throw new Exception("Fixture $name not found ($filename)");
		}

		$class_name = $options["class_name"];
		if(is_null($class_name)){
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
			}else{
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

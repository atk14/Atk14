<?php
class Atk14Fixtures {

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
			"model_class" => null, // ""
		);

		$filename = realpath(sprintf("%s/../test/fixtures/%s.yml", $ATK14_GLOBAL->getApplicationPath(), $name));
		if (!file_exists($filename)) {
			throw new Exception("Fixture $name not found");
		}

		$model_class = $options["model_class"];
		if(is_null($model_class)){
			$model_class = String4::ToObject($name)->singularize()->camelize()->toString(); // "gallery_items" -> "GalleryItem"
			if(!class_exists($model_class) || !method_exists($model_class,'CreateNewRecord')){
				$model_class = "";
			}
		}

		$yml = miniYAML::Load(Files::GetFileContent($filename));

		$list = new Atk14FixturesList($model_class);
		foreach($yml as $k => $values) {
			$o = $model_class::CreateNewRecord($values);
			//$list[$k] = $o->getId();
			$list[$k] = $o;
		}

		return $list;
	}
}

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
	static function Load($name){
		global $ATK14_GLOBAL;

		$filename = realpath(sprintf("%s/../test/fixtures/%s.yml", $ATK14_GLOBAL->getApplicationPath(), $name));
		if (!file_exists($filename)) {
			throw new Exception("Fixture $name not found");
		}

		$class_name = new String4($name);
		$model_class_name = $class_name->singularize()->camelize()->toString(); // "gallery_items" -> "GalleryItem"

		$yml = miniYAML::Load(Files::GetFileContent($filename));

		$list = new Atk14FixturesList($model_class_name);
		foreach($yml as $k => $values) {
			$o = $model_class_name::CreateNewRecord($values);
			//$list[$k] = $o->getId();
			$list[$k] = $o;
		}
		return $list;
	}
}

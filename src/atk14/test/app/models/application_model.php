<?php
// !! This is not a TableRecord class
class ApplicationModel extends Dictionary {

	static function CreateNewRecord($values){
		static $id = 1;
		
		$values += array(
			"id" => null,
		);

		if(is_null($values["id"])){
			$values["id"] = $id;
			$id++;
		}

		$object = new static($values);
		return $object;
	}

	function getId(){
		return (int)$this["id"];
	}

	function __call($name,$arguments){
		$name = new String4($name);
		if($name->match("/^get(.+)/",$matches)){
			$field = $matches[1]->underscore()->toString();
			if($this->keyPresents($field)){
				return $this[$field];
			}
		}

		throw new Exception("TableRecord::__call(): unknown method ".get_class($this)."::$name()");
	}
}

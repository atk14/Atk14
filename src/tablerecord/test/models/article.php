<?php
class Article extends TableRecord{

	function __construct(){
		parent::__construct("articles",array(
			"do_not_read_values" => array("body")
		));
	}

	static function GetNextId(){
		$id = self::GetSequenceNextval();
		return $id * 1000;
	}

	function getAuthorsLister(){
		return $this->getLister("Authors");
	}

	function getAuthors(){
		$lister = $this->getAuthorsLister();
		return $lister->getRecords();
	}
}

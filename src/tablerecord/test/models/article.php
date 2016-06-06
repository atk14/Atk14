<?php
class Article extends TableRecord{
	function __construct(){
		parent::__construct("articles");
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

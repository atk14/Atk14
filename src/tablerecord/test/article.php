<?php
class Article extends TableRecord{
	function __construct(){
		parent::__construct("articles");
	}
	function getAuthorsLister(){
		return $this->getLister("Authors");
	}

	function getAuthors(){
		$lister = $this->getAuthorsLister();
		return $lister->getRecords();
	}
}

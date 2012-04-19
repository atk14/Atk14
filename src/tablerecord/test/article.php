<?php
class Article extends TableRecord{
	function getAuthorsLister(){
		return $this->getLister("Authors");
	}

	function getAuthors(){
		$lister = $this->getAuthorsLister();
		return $lister->getRecords();
	}
}

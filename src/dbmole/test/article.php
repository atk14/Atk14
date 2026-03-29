<?php
class Article {

	protected $id;

	function __construct($id = 1){
		$this->id = (int)$id;
	}

	function getId(){
		return $this->id;
	}
}

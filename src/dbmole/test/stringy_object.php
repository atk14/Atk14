<?php
class StringyObject {

	protected $str;

	function __construct($str){
		$this->str = $str;
	}

	function __toString(){
		return (string)$this->str;
	}
}

<?php
if(!class_exists("TcSuperBase")){
	class TcSuperBase{ }
}

class TcController extends TcSuperBase{
	var $namespace = null;
	var $dbmole = null;
	var $client = null;

	function __construct(){
		$ref = new ReflectionClass("TcSuperBase");
		$ref->newInstance(func_get_args());

		$this->dbmole = $GLOBALS["dbmole"];
		$this->client = new Atk14Client();
	}	
}
// This is under heavy development :)

<?php
if(!class_exists("TcSuperBase")){
	class TcSuperBase{ }
}

class TcAtk14Controller extends TcSuperBase{
	var $namespace = "";
	var $dbmole = null;
	var $client = null;

	function __construct(){
		$ref = new ReflectionClass("TcSuperBase");
		$ref->newInstance(func_get_args());

		$this->dbmole = $GLOBALS["dbmole"];
		$this->client = new Atk14Client();

		if(isset($GLOBALS["_TEST"]) && preg_match('@test/controllers/([^/]+)/[a-z_]+.(php|inc)@',$GLOBALS["_TEST"]["FILENAME"],$matches)){
			$this->namespace = $matches[1];
		}
		
		$this->client->namespace = $this->namespace;
	}	
}
// This is under heavy development :)

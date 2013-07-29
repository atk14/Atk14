<?php
if(!class_exists("TcSuperBase")){
	class TcSuperBase{ }
}


class TcAtk14Model extends TcSuperBase{
	var $dbmole = null;

	function __construct(){
		$ref = new ReflectionClass("TcSuperBase");
		$ref->newInstance(func_get_args());

		$this->dbmole = $GLOBALS["dbmole"];
	}
}

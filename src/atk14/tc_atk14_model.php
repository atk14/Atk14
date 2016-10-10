<?php
if(!class_exists("TcSuperBase")){
	class TcSuperBase{ }
}

class TcAtk14Model extends TcSuperBase{
	var $dbmole = null;

	function __construct($name = NULL, array $data = array(), $dataName = ''){
		$this->dbmole = $GLOBALS["dbmole"];
		parent::__construct($name, $data, $dataName);
	}
}

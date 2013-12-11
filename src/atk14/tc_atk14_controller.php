<?php
if(!class_exists("TcSuperBase")){
	class TcSuperBase{ }
}

/**
 * This is a base class convenient for controller testing
 *
 * Controller tests are usually located in ./test/controllers/
 */
class TcAtk14Controller extends TcSuperBase{
	var $namespace = "";
	var $session = null;
	var $dbmole = null;
	var $client = null;

	function __construct(){
		$ref = new ReflectionClass("TcSuperBase");
		$ref->newInstance(func_get_args());

		$this->session = $GLOBALS["ATK14_GLOBAL"]->getSession();
		$this->dbmole = $GLOBALS["dbmole"];
		$this->client = new Atk14Client();

		// $this->namespace is determined by a directory of the current test file
		// ./test/controllers/admin/tc_users.php -> $this->namespace = "admin"
		if(isset($GLOBALS["_TEST"]) && preg_match('@test/controllers/([^/]+)/[a-z0-9_]+.(php|inc)@',$GLOBALS["_TEST"]["FILENAME"],$matches)){
			$this->namespace = $matches[1];
		}

		$this->client->namespace = $this->namespace;
	}	
}

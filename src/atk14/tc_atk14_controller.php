<?php
/**
 * This is a base class convenient for controller testing
 *
 * Controller tests are usually located in ./test/controllers/
 */
class TcAtk14Controller extends TcAtk14Base{

	public $namespace = "";
	public $session = null;
	public $client = null;

	function __construct($name = NULL, array $data = [], $dataName = ''){
		parent::__construct($name, $data, $dataName);

		$this->session = $GLOBALS["ATK14_GLOBAL"]->getSession();
		$this->client = new Atk14Client();

		// $this->namespace is determined by a directory of the current test file
		// ./test/controllers/admin/tc_users.php -> $this->namespace = "admin"
		if(isset($GLOBALS["_TEST"]) && preg_match('@test/controllers/([^/]+)/[a-z0-9_]+.(php|inc)@',$GLOBALS["_TEST"]["FILENAME"],$matches)){
			$this->namespace = $matches[1];
		}

		$this->client->namespace = $this->namespace;
	}
}

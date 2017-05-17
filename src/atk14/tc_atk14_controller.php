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

	function __construct($name = NULL, array $data = array(), $dataName = ''){
		parent::__construct($name, $data, $dataName);

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

	/**
	 *
	 * @see TcAtk14Model::setUpFixtures()
	 */
	function setUpFixtures() {
		$annotations = $this->getAnnotations();
		if (!isset($annotations["class"]["fixture"])) {
			return;
		}

		foreach($annotations["class"]["fixture"] as $_f) {
			$this->$_f = $this->loadFixture($_f);
		}
	}

	/**
	 *
	 * @see TcAtk14Model::loadFixture()
	 */
	function loadFixture($name){
		return Atk14Fixture::Load($name);
	}
}

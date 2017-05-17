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

	function setUp(){
		$this->dbmole->begin();
		$this->setUpFixtures();
		parent::setUp();
	}

	function tearDown(){
		$this->dbmole->rollback();
		parent::tearDown();
	}

	/**
	 * Loads fixtures according to class annotations (@fixture)
	 *
	 * Example usage:
	 *
	 * A fixture file:
	 * ```
	 * # file: test/fixtures/users.yml
	 *
	 * rocky:
	 *   login: "rocky"
	 *   name: "Rocky Balboa"
	 *   password: "secret"
	 *
	 * arnie:
	 *   login: "arnie"
	 *   name: "Arnie S."
	 *   password: "noMercy"
	 * ```
	 *
	 * A test case:
	 * ```
	 * <?php
	 * // file: test/models/tc_user.php
	 * /**
	 *  *
	 *  * @fixture users
	 * \*\/
	 * class TcUser extends TcBase {
	 *
	 *  function test_something() {
	 *   $this->assertNotNull($this->users["rocky"]);
	 *   $this->assertEquals("Rocky Balboa",$this->users->getName());
	 *  }
	 * }
	 * ```
	 *
	 * The base class should provide fixtures loading automation:
	 * ```
	 * <?php
	 * // file: test/models/tc_base.php
	 * class TcBase extends TcAtk14Model {
	 *
	 *  function setUp(){
	 *    $this->dbmole->begin();
	 *    $this->setUpFixtures();
	 *  }
	 *
	 *  function tearDown(){
	 *    $this->dbmole->rollback();
	 *  }
	 * }
	 * ```
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
	 * Loads the specific fixture
	 *
	 * ```
	 * $this->users = $this->loadFixture("users"); // loads data from test/fixtures/users.yml
	 * ```
	 */
	function loadFixture($name){
		return Atk14Fixture::Load($name);
	}

}

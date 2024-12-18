<?php
if(!class_exists("TcSuperBase")){
	class TcSuperBase{ }
}

/**
 * Common base class for other ATK14 testing classes
 */
 
#[\AllowDynamicProperties]
class TcAtk14Base extends TcSuperBase {

	var $dbmole = null;

	function _setUp(){
		$this->dbmole->begin(array(
			"execute_after_connecting" => true,
		));
		$this->setUpFixtures();
		parent::_setUp();
	}

	function _tearDown(){
		$this->dbmole->rollback();
		parent::_tearDown();
	}
	
	function __construct($name = NULL, array $data = array(), $dataName = ''){
		parent::__construct($name, $data, $dataName);

		// The '&' here is required. Without it, the delayed transaction beginning in begin() method doesn't work.
		// Actually don't know why is that.
		$this->dbmole = &$GLOBALS["dbmole"];
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
	 *  function _setUp(){
	 *    $this->dbmole->begin();
	 *    $this->setUpFixtures();
	 *  }
	 *
	 *  function _tearDown(){
	 *    $this->dbmole->rollback();
	 *  }
	 * }
	 * ```
	 */
	function setUpFixtures() {
		Atk14Fixture::ClearLoadedFixtures();

		$annotations = $this->getAnnotations();
		if (!isset($annotations["class"]["fixture"])) {
			return;
		}

		foreach($annotations["class"]["fixture"] as $_f) {
			$this->$_f = $this->loadFixture($_f,array("reload_fixture" => false));
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

	function getAnnotations():array{
		if(is_callable("parent::getAnnotations")){
			// in case of PHPUnit <= 8.*
			return parent::getAnnotations();
		}

		$annotations = array("class" => array());

		$ref = new ReflectionClass(get_class($this));
		$docComment = $ref->getDocComment();
		preg_match_all('/@(\w+)\s+(.*)/', $docComment, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			if(!isset($annotations["class"][$match[1]])){
				$annotations["class"][$match[1]] = array();
			}
			$annotations["class"][$match[1]][] = trim($match[2]);
		}

		return $annotations;
	}
}

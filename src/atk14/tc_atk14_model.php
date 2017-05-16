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
	 * Nacteni fixtur
	 *
	 * Pouziti potrebnych fixtur se zajisti pouzitim anotace '@fixture table_name' pred deklaraci tridy.
	 * Soubor s fixturami je v test/fixtures/table_name.yml
	 *
	 * == Priklad pouziti fixtur pro model Showroom
	 *
	 * YML soubor showrooms.yml:
	 * ```
	 * letnany:
	 *  name_cs: Prodejna Letňany
	 *  location_code: 60
	 *  contact_email: letnany@activacek.cz
	 *
	 * brno:
	 *  name_cs: Pobočka Brno
	 *  location_code: 80
	 *  contact_email: brno@activacek.cz
	 * ```
	 *
	 *
	 * ```
	 * /**
	 *  * @fixture showrooms
	 * \*\/
	 * class TcShowrooms extends TcBase {
	 * 	function test_something() {
	 * 		$this->assertNotNull($this->showrooms["brno"]);
	 * 		$this->showrooms["brno"]->getId();
	 *
	 *
	 * 	}
	 * }
	 * ```
	 */
	function setUpFixtures() {
		global $ATK14_GLOBAL;

		$annotations = $this->getAnnotations();
		if (!isset($annotations["class"]["fixture"])) {
			return;
		}

		foreach($annotations["class"]["fixture"] as $_f) {
			$this->$_f = $this->loadFixture($_f);
		}
	}

	/**
	 * $this->users = $this->loadFixture("users");
	 */
	function loadFixture($name){
		return Atk14Fixture::Load($name);
	}

}

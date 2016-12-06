<?php
class TcUnitBase extends TcAtk14Model {}

class TcModel extends TcUnitBase {
	/**
	 * Otestovani, ze funguje pouzivani datovych sad pomoci anotace @dataProvider
	 *
	 * @dataProvider provideNumbers
	 */
	function testSomething($a, $b) {
		$this->assertTrue(isset($a));
		$this->assertTrue(isset($b));
		$this->assertEquals(2 + $a, $b);
	}

	function provideNumbers() {
		return array(
			"ada" => array(0,2),
			array(1,3),
			array(5,7),
		);
	}
}


<?php
class TcHttpUtils extends tc_base{
	function test__StripslashesArray(){
		$ar = array("a" => "b", "c" => "d");
		$this->assertEquals($ar,_HTTPUtils::_StripslashesArray($ar));

		$ar = array("a" => "b", "c" => "d", "e" => '\\"Very nice\\"');
		$this->assertEquals(array("a" => "b", "c" => "d", "e" => '"Very nice"'),_HTTPUtils::_StripslashesArray($ar));
	}

	function test__SetAuthData(){
		global $_SERVER;

		$_SERVER["PHP_AUTH_USER"] = null;
		$_SERVER["PHP_AUTH_PW"] = null;

		_HTTPUtils::_SetAuthData("");
		$this->assertEquals(null,$_SERVER["PHP_AUTH_USER"]);
		$this->assertEquals(null,$_SERVER["PHP_AUTH_PW"]);

		_HTTPUtils::_SetAuthData("Nonsence");
		$this->assertEquals(null,$_SERVER["PHP_AUTH_USER"]);
		$this->assertEquals(null,$_SERVER["PHP_AUTH_PW"]);


		// a valid Authorization value
		_HTTPUtils::_SetAuthData("Basic cHJldmlldzpWdVNlMXk=");
		$this->assertEquals("preview",$_SERVER["PHP_AUTH_USER"]);
		$this->assertEquals("VuSe1y",$_SERVER["PHP_AUTH_PW"]);
	}
}

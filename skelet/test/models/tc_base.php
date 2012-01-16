<?php
class tc_base extends tc_super_base{
	function setUp(){
		$GLOBALS["dbmole"]->begin();
	}

	function tearDown(){
		global $dbmole;
		$GLOBALS["dbmole"]->rollback();
	}
}

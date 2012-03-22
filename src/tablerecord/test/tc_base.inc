<?php
class tc_base extends tc_super_base{
	function setUp(){
		Cache::Clear();
		$GLOBALS["dbmole"]->begin();
	}
 
	function tearDown(){
		$GLOBALS["dbmole"]->rollback();
	}
}

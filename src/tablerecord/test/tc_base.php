<?php
class tc_base extends tc_super_base{
	function setUp(){
		Cache::Clear();
		$GLOBALS["dbmole"]->begin();
		$GLOBALS["dbmole"]->doQuery("DELETE FROM test_table");
	}
 
	function tearDown(){
		$GLOBALS["dbmole"]->rollback();
	}
}

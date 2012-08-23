<?php
class TcBase extends tc_super_base{
	function setUp(){
		Cache::Clear();
		$this->dbmole = $GLOBALS["dbmole"];

		$this->dbmole->begin();
		$this->_empty_test_table();
	}
 
	function tearDown(){
		$this->dbmole->rollback();
	}

	function _empty_test_table(){
		$this->dbmole->doQuery("DELETE FROM test_table");
	}
}

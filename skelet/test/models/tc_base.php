<?php
class TcBase extends TcSuperBase{
	function setUp(){
		$GLOBALS["dbmole"]->begin();
	}

	function tearDown(){
		global $dbmole;
		$GLOBALS["dbmole"]->rollback();
	}
}

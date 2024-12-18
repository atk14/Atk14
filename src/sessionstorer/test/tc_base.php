<?php
class TcBase extends TcSuperbase{

	function _setUp(){
		global $_COOKIE, $dbmole;

		if(!isset($_COOKIE)){ $_COOKIE = array(); }
		$_COOKIE = array();
		$this->dbmole = &$dbmole;
		$this->dbmole->begin();
	}

	function _tearDown(){
		$this->dbmole->rollback();
	}
}

<?php
/**
 * The base class of every model`s test case class.
 *
 * Notice that TcBase is descendant of TcAtk14Model
 * so there are a couple of special member variables in advance.
 */
class TcBase extends TcAtk14Model{

	function setUp(){
		$this->dbmole->begin();
	}

	function tearDown(){
		$this->dbmole->rollback();
	}
}

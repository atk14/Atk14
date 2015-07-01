<?php
class tc_base extends tc_super_base{
	function setUp(){
		$m = &$this->_get_moles();
		$this->my = $m["my"];
		$this->pg	= $m["pg"];
		//$this->ora = $m["ora"];
		$this->base = $m["base"];
	}

	function tearDown(){
	}

	/**
	 * $this->_execute_with_error($dbmole,"doQuery","SELECT * FROM test_table WHERE title=:title",array(":title" => "Nice title"));
	 */
	function _execute_with_error(){
		$params = func_get_args();
		$dbmole = array_shift($params);
		$method = array_shift($params);

		$exception_thrown = false;
		$error_message = "";
		try{
			call_user_func_array(array($dbmole,$method),$params);
		}catch(Exception $e){
			$exception_thrown = true;
			$error_message = $e->getMessage();
		}
		$this->assertTrue($exception_thrown);

		return $error_message;
	}

	function &_get_moles(){
		$out = array(
			"base" => DbMole::GetInstance(),
		);	
		$out["my"] = &MysqlMole::GetInstance();
		$out["pg"] = &PgMole::GetInstance();
		//$out["ora"] = &OracleMole::GetInstance();
		return $out;
	}

	function &_get_real_moles(){
		$moles = $this->_get_moles();
		unset($moles["base"]);
		return $moles;
	}

	/**
	 * $dbmole = $this->_get_mole("pg")
	 */
	function &_get_mole($key){
		$moles = $this->_get_moles();
		return $moles[$key];
	}
}

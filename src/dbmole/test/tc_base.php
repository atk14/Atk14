<?php
class tc_base extends tc_super_base{
	function setUp(){
		$m = &$this->_get_moles();
		$this->my = $m["my"];
		$this->pg	= $m["pg"];
		$this->ora = $m["ora"];
		$this->base = $m["base"];
	}

	function tearDown(){
	}

	function &_get_moles(){
		$out = array(
			"base" => new DbMole(),
		);	
		$out["my"] = &MysqlMole::GetInstance();
		$out["pg"] = &PgMole::GetInstance();
		$out["ora"] = &OracleMole::GetInstance();
		return $out;
	}
}

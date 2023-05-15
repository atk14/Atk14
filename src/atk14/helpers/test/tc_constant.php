<?php
class TcConstant extends TcBase {

	function test(){
		define("XXX","xxx!");
		$this->assertEquals("xxx!",smarty_modifier_constant("XXX"));
	}
}

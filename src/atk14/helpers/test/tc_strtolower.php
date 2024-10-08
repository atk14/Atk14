<?php
class TcStrtolower extends TcBase {

	function test(){
		$this->assertEquals("hello!",smarty_modifier_strtolower("Hello!"));
		$this->assertEquals("",smarty_modifier_strtolower(""));
		$this->assertEquals("",smarty_modifier_strtolower(null));
	}
}

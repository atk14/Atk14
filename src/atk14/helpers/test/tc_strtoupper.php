<?php
class TcStrtoupper extends TcBase {

	function test(){
		$this->assertEquals("HELLO!",smarty_modifier_strtoupper("Hello!"));
		$this->assertEquals("",smarty_modifier_strtoupper(""));
		$this->assertEquals("",smarty_modifier_strtoupper(null));
	}
}

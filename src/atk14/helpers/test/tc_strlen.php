<?php
class TcStrlen extends TcBase {

	function test(){
		$this->assertEquals(5,smarty_modifier_strlen("Hello"));
		$this->assertEquals(0,smarty_modifier_strlen(""));
		$this->assertEquals(0,smarty_modifier_strlen(null));
		$this->assertEquals(3,smarty_modifier_strlen(123));
	}
}

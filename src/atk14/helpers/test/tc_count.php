<?php
class TcCount extends TcBase {

	function test(){
		$this->assertEquals(1,smarty_modifier_count(array("item")));
		$this->assertEquals(0,smarty_modifier_count(array()));
		$this->assertEquals(0,smarty_modifier_count(null));
	}
}

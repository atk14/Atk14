<?php
class TcCount extends TcBase {

	function test(){
		$this->assertEquals(1,smarty_modifier_count(["item"]));
		$this->assertEquals(0,smarty_modifier_count([]));
		$this->assertEquals(0,smarty_modifier_count(null));
	}
}

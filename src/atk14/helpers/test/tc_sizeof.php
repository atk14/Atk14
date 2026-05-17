<?php
class TcSizeof extends TcBase {

	function test(){
		$this->assertEquals(1,smarty_modifier_sizeof(["item"]));
		$this->assertEquals(0,smarty_modifier_sizeof([]));
		$this->assertEquals(0,smarty_modifier_sizeof(null));
	}
}

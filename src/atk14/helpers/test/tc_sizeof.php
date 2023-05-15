<?php
class TcSizeof extends TcBase {

	function test(){
		$this->assertEquals(1,smarty_modifier_sizeof(array("item")));
		$this->assertEquals(0,smarty_modifier_sizeof(array()));
		$this->assertEquals(0,smarty_modifier_sizeof(null));
	}
}

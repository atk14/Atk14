<?php
class TcDate extends TcBase {

	function test(){
		$this->assertEquals(date("Y"),smarty_modifier_date("Y"));
		$this->assertEquals("1970",smarty_modifier_date("Y",123));
	}
}

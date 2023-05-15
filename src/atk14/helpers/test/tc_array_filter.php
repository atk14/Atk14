<?php
class TcArrayFilter extends TcBase {

	function test(){
		$this->assertEquals([0 => "a", 4 => "x"],smarty_modifier_array_filter(["a",null,"",0,"x"]));
	}
}

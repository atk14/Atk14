<?php
class TcJoin extends TcBase {

	function test(){
		$this->assertEquals("a,b",smarty_modifier_join(",",["a","b"]));
	}
}

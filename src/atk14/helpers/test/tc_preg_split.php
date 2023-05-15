<?php
class TcPregSplit extends TcBase {

	function test(){
		$this->assertEquals(["a","b","c"],smarty_modifier_preg_split("/,/","a,b,c"));
	}
}

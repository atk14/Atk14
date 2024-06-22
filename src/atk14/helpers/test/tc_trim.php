<?php
class TcTrim extends TcBase {

	function test(){
		$template = null;
		$repeat = false;

		// block helper
		$this->assertEquals("Content",smarty_block_trim([],"\n\n Content \n\n",$template,$repeat));
		$this->assertEquals("Line1
			Line2
			Line3",smarty_block_trim([],"
			Line1
			Line2
			Line3
		",$template,$repeat));
		$this->assertEquals("Line1
Line2
Line3",smarty_block_trim(["each_line" => true],"
			Line1
			Line2
			Line3
		",$template,$repeat));

		// modifier
		/*
		$this->assertEquals("Content",smarty_modifier_trim("\n\n Content \n\n"));
		$this->assertEquals("Line1
			Line2
			Line3",smarty_modifier_trim("
			Line1
			Line2
			Line3
		"));
		*/
		$this->assertEquals("Line1
Line2
Line3",smarty_modifier_trim("
			Line1
			Line2
			Line3
		","each_line=true"));

	}
}

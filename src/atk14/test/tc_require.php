<?php
class TcRequire extends TcBase {

	function test(){

		$this->assertEquals(false,function_exists("smarty_modifier_sample_filter"));

		$ret = Atk14Require::Helper("modifier.sample_filter");
		$this->assertEquals(1,sizeof($ret));

		$this->assertEquals(true,function_exists("smarty_modifier_sample_filter"));

		$ret = Atk14Require::Helper("modifier.sample_filter");
		$this->assertEquals(array(),$ret);

		// 

		$this->assertEquals(false,function_exists("smarty_block_sample_block"));

		$ret = Atk14Require::Helper("block.sample_block.php");
		$this->assertEquals(1,sizeof($ret));

		$this->assertEquals(true,function_exists("smarty_block_sample_block"));

		$ret = Atk14Require::Helper("block.sample_block.php");
		$this->assertEquals(array(),$ret);
	}
}

<?php
class TcNl2br extends TcBase {

	function test(){
		$template = null;
		$repeat = false;

		$this->assertEquals('Hello World!',smarty_block_nl2br([],"Hello World!",$template,$repeat));
		$this->assertEquals("Hello<br />\n World!",smarty_block_nl2br([],"Hello\n World!",$template,$repeat));
	}
}

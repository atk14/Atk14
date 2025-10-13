<?php
class TcStyleTag extends TcBase {

	function test(){
		$template = null;
		$repeat = false;

		$this->assertEquals('<style>h1 { color: red; }</style>',smarty_block_style_tag(array(),'h1 { color: red; }',$template,$repeat));
	}
}

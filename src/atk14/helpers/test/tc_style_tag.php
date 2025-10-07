<?php
class TcStyleTag extends TcBase {

	function test(){
		global $ATK14_GLOBAL;
		$template = null;
		$repeat = false;

		$this->assertEquals('<style>h1 { color: red; }</style>',smarty_block_style_tag(array(),'h1 { color: red; }',null,$repeat));

		// Content-Security-Policy nonce

		$ATK14_GLOBAL->setCspNonce("abcdefgh");

		$this->assertEquals('<style nonce="abcdefgh">h1 { color: red; }</style>',smarty_block_style_tag(array(),'h1 { color: red; }',null,$repeat));
	}
}

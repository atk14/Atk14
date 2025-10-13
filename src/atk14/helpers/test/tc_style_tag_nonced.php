<?php
// See initialize.php for seeting constant CSP_NONCE
class TcStyleTagNonced extends TcBase {

	function test(){
		$template = null;
		$repeat = false;

		// Content-Security-Policy nonce

		$this->assertEquals('<style nonce="abcdefgh">h1 { color: red; }</style>',smarty_block_style_tag(array(),'h1 { color: red; }',$template,$repeat));
	}
}

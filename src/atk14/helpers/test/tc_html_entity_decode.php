<?php
class TcHtmlEntityDecode extends TcBase {

	function test(){
		$this->assertEquals("Hi >",smarty_modifier_html_entity_decode('Hi &gt;'));
	}
}

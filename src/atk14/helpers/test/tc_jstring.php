<?php
class TcJstring extends TcBase {

	function test(){
		$template = null;
		$repeat = false;

		$this->assertEquals('"<h1 class=\"heading\">Hello World!</h1>"',smarty_block_jstring(array(),'<h1 class="heading">Hello World!</h1>',$template,$repeat));

		// empty string
		$this->assertEquals('""',smarty_block_jstring(array(),'',$template,$repeat));
	
		// <script> tag special care
		$this->assertEquals('"<scr" + "ipt></scr" + "ipt>"',smarty_block_jstring(array(),'<script></script>',$template,$repeat));
		$this->assertEquals('"<script></script>"',smarty_block_jstring(array("escape" => "json"),'<script></script>',$template,$repeat));
	}
}

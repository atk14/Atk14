<?php
class TcCamelize extends TcBase{
	function test(){
		$this->assertEquals("HelloWorld",smarty_modifier_camelize("hello_world"));
		$this->assertEquals("HelloWorld",smarty_modifier_camelize("hello_world","upper"));
		$this->assertEquals("helloWorld",smarty_modifier_camelize("hello_world","lower"));
	}
}

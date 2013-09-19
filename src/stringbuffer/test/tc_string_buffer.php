<?php
class TcStringBuffer extends TcBase{
	function test__toString(){
		$buffer = new StringBuffer("Hello World!");

		$this->assertEquals("Hello World!",$buffer->toString());
		$this->assertEquals("Hello World!","$buffer");
	}
}

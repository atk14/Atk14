<?php
class TcStringBuffer extends TcBase{
	function test__toString(){
		$buffer = new StringBuffer("Hello World!");

		$this->assertEquals("Hello World!",$buffer->toString());
		$this->assertEquals("Hello World!","$buffer");
	}

	function test(){
		$buffer = new StringBuffer("Just");
		$buffer->addString(" a Simple");
		$buffer->addString("\nTest");
		$this->assertEquals("Just a Simple\nTest","$buffer");
		$this->assertEquals(18,$buffer->getLength());

		$lorem = new StringBuffer();
		$lorem->addFile("lorem.txt");
		$this->assertEquals("lorem ipsum dolor sit amet","$lorem");
		$this->assertEquals(26,$lorem->getLength());

		$buffer->addStringBuffer($lorem);
		$this->assertEquals("Just a Simple\nTestlorem ipsum dolor sit amet","$buffer");
		$this->assertEquals(44,$buffer->getLength());

		$buffer->clear();
		$this->assertEquals(0,$buffer->getLength());
		$this->assertEquals("","$buffer");
	}

	function test_printOut(){
		$buffer = new StringBuffer("BEGIN");
		$buffer->addString(" ");
		$buffer->addFile("lorem.txt");
		$buffer->addString(" END");

		ob_start();
		$buffer->printOut();
		$content = ob_get_clean();

		$this->assertEquals("BEGIN lorem ipsum dolor sit amet END",$content);
	}

	function test_replace(){
		$buffer = new StringBuffer("Young man was coding in ATK14");
		$buffer->addString(" for freedom.");
		$buffer->addString(" Young man lived happily ever after.");

		$buffer->replace("man","woman");
		$this->assertEquals("Young woman was coding in ATK14 for freedom. Young woman lived happily ever after.","$buffer");

		$buffer = new StringBuffer();
		$buffer->addFile("lorem.txt");
		$this->assertEquals(26,$buffer->getLength());

		$buffer->replace("lorem","lorem what?");
		$this->assertEquals("lorem what? ipsum dolor sit amet","$buffer");
		$this->assertEquals(32,$buffer->getLength());

		$buffer->replace("ipsum","ipsum what?");
		$this->assertEquals("lorem what? ipsum what? dolor sit amet","$buffer");
		$this->assertEquals(38,$buffer->getLength());

		ob_start();
		$buffer->printOut();
		$content = ob_get_clean();

		$this->assertEquals("lorem what? ipsum what? dolor sit amet",$content);
	}
}

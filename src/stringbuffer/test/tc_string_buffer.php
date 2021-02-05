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

	function test_substr(){
		$buffer1 = new StringBuffer("Hello World!");

		$buffer2 = new StringBuffer();
		$buffer2->addString("");
		$buffer2->addString("Hello");
		$buffer2->addString("");
		$buffer2->addString(" ");
		$buffer2->addString("World");
		$buffer2->addString("!");
		$buffer2->addString("");

		foreach(array($buffer1,$buffer2) as $buffer){

			$this->assertEquals("",$buffer->substr(0,0));
			$this->assertEquals("Hello",$buffer->substr(0,5));
			$this->assertEquals("ll",$buffer->substr(2,2));
			$this->assertEquals("o W",$buffer->substr(4,3));
			$this->assertEquals("Hello World",$buffer->substr(0,11));
			$this->assertEquals("Hello World!",$buffer->substr(0,12));
			$this->assertEquals("Hello World!",$buffer->substr(0,13));
			$this->assertEquals("Hello World!",$buffer->substr(0));
			$this->assertEquals("Hello World!",$buffer->substr(-12));
			$this->assertEquals("Hello World!",$buffer->substr(-12,12));
			$this->assertEquals("Hello World!",$buffer->substr(-1000,12));
			$this->assertEquals("World!",$buffer->substr(6));
			$this->assertEquals("!",$buffer->substr(-1));
			$this->assertEquals("ld!",$buffer->substr(-3));
			$this->assertEquals("Hello World!",$buffer->substr(-100));

		}

		$buffer = new StringBuffer();
		$buffer->addString("START ");
		$buffer->addFile("lorem.txt");
		$buffer->addString(" END");
		//
		$this->assertEquals("START lorem ipsum dolor sit amet END",$buffer->substr(0));
		$this->assertEquals("START lorem",$buffer->substr(0,11));
		$this->assertEquals("ART lorem i",$buffer->substr(2,11));
		$this->assertEquals("END",$buffer->substr(-3));
		$this->assertEquals("amet END",$buffer->substr(-8));

		$buffer = new StringBuffer();
		$buffer->addFile("lorem.txt");
		$buffer->addFile("lorem.txt");
		//
		$this->assertEquals("lorem ipsum dolor sit ametlorem ipsum dolor sit amet",$buffer->substr(0));
		$this->assertEquals("lorem ipsum",$buffer->substr(0,11));
		$this->assertEquals("sit ametlorem ipsum dolor sit amet",$buffer->substr(18));
		$this->assertEquals("r sit amet",$buffer->substr(-10));

		// Zeroes
		$zero = chr(0);
		$buffer = new StringBuffer();
		$buffer->addString("START{$zero}-");
		$buffer->addFile(__DIR__ . "/zeroes.dat");
		$buffer->addString("-{$zero}END");
		$this->assertEquals(22,$buffer->getLength());
		$this->assertEquals("START{$zero}-{$zero}{$zero}{$zero}{$zero}{$zero}{$zero}{$zero}{$zero}{$zero}{$zero}-{$zero}END",$buffer->substr(0));
		$this->assertEquals("START{$zero}",$buffer->substr(0,6));
		$this->assertEquals("START{$zero}-{$zero}",$buffer->substr(0,8));
		$this->assertEquals("{$zero}END",$buffer->substr(-4));
		$this->assertEquals("{$zero}-{$zero}END",$buffer->substr(-6));
	}
}

<?php
class TcStringBufferTemporary extends TcBase {

	function test(){
		$buffer = new StringBufferTemporary("Hello World!");
		$buffer->addString(" Hello again!");
		$this->assertEquals("Hello World! Hello again!",$buffer);

		$items = $buffer->getItems();
		$this->assertEquals(2,sizeof($items));

		$this->assertTrue(is_a($items[0],"StringBufferTemporaryItem"));
		$this->assertTrue(is_a($items[1],"StringBufferTemporaryItem"));

		$filename1 = $items[0]->getFilename();
		$filename2 = $items[1]->getFilename();

		$this->assertTrue(file_exists($filename1));
		$this->assertTrue(file_exists($filename1));

		unset($items);
		unset($buffer);

		// files must be deleted in __destruct()
		$this->assertFalse(file_exists($filename1));
		$this->assertFalse(file_exists($filename1));

		//

		$buffer = new StringBufferTemporary("Hi there!");
		$this->assertEquals("Hi there!","$buffer");
		$items = $buffer->getItems();
		$this->assertEquals(1,sizeof($items));
		$filename = $items[0]->getFilename();
		$this->assertTrue(file_exists($filename));

		$buffer = serialize($buffer);
		$buffer = unserialize($buffer);

		// file must be deleted in __sleep()
		$this->assertFalse(file_exists($filename));
		// and its content must be loaded as member variable
		$this->assertEquals("Hi there!","$buffer");

		// The last item is being written to a temporary file automatically.
		// In testing, after 5 bytes.
		$buffer = new StringBufferTemporary();

		$items = $buffer->getItems();
		$this->assertEquals(0,sizeof($items));

		$buffer->addString("A");
		$items = $buffer->getItems();
		$this->assertEquals(1,sizeof($items));
		$this->assertEquals("A",(string)$items[0]);
		$this->assertEquals(false,$items[0]->isFileized());

		$buffer->addString("B");
		$items = $buffer->getItems();
		$this->assertEquals(1,sizeof($items));
		$this->assertEquals("AB",(string)$items[0]);
		$this->assertEquals(false,$items[0]->isFileized());

		$buffer->addString("CDEF");
		$items = $buffer->getItems();
		$this->assertEquals(1,sizeof($items));
		$this->assertEquals("ABCDEF",(string)$items[0]);
		$this->assertEquals(true,$items[0]->isFileized());

		$buffer->addString("GH");
		$items = $buffer->getItems();
		$this->assertEquals(2,sizeof($items));
		$this->assertEquals("ABCDEF",(string)$items[0]);
		$this->assertEquals(true,$items[0]->isFileized());
		$this->assertEquals("GH",(string)$items[1]);
		$this->assertEquals(false,$items[1]->isFileized());

		$buffer->addString("IJKLM");
		$items = $buffer->getItems();
		$this->assertEquals(2,sizeof($items));
		$this->assertEquals("ABCDEF",(string)$items[0]);
		$this->assertEquals(true,$items[0]->isFileized());
		$this->assertEquals("GHIJKLM",(string)$items[1]);
		$this->assertEquals(true,$items[1]->isFileized());
		
		$buffer->addString("NOPQRST");
		$items = $buffer->getItems();
		$this->assertEquals(3,sizeof($items));
		$this->assertEquals("ABCDEF",(string)$items[0]);
		$this->assertEquals(true,$items[0]->isFileized());
		$this->assertEquals("GHIJKLM",(string)$items[1]);
		$this->assertEquals(true,$items[1]->isFileized());
		$this->assertEquals("NOPQRST",(string)$items[2]);
		$this->assertEquals(true,$items[2]->isFileized());
	}
}

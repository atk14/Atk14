<?php
class TcStringBufferTemporary extends TcBase {

	function test(){
		$buffer = new StringBufferTemporary("Hello World!");
		$buffer->addString(" Hello again!");
		$this->assertEquals("Hello World! Hello again!",$buffer);

		$items = $buffer->getItems();
		$this->assertEquals(2,sizeof($items));

		$this->assertTrue(is_a($items[0],"StringBufferTemporaryFileItem"));
		$this->assertTrue(is_a($items[1],"StringBufferTemporaryFileItem"));

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
	}
}

<?php
class TcFileField extends TcBase{
	function test__fileSize2Int(){
		$f = new FileField(array());

		$this->assertEquals(null,$f->_fileSize2Int(null));
		$this->assertEquals(null,$f->_fileSize2Int(""));
		$this->assertEquals(null,$f->_fileSize2Int("  "));

		$this->assertEquals(1000,$f->_fileSize2Int(" 1000 "));
		$this->assertEquals(1024,$f->_fileSize2Int("1kB"));
		$this->assertEquals(1024,$f->_fileSize2Int("1k"));

		$this->assertEquals(1048576,$f->_fileSize2Int("1MB"));
		$this->assertEquals(1048576,$f->_fileSize2Int("1M"));
		$this->assertEquals(1048576,$f->_fileSize2Int("1 MB"));
		$this->assertEquals(1048576,$f->_fileSize2Int(" 1 MB "));

		$this->assertEquals(2621440,$f->_fileSize2Int("2.5 MB "));
	}
}

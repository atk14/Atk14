<?php
class TcZz02Unserialize extends TcBase {

	function test(){
		$filename = __DIR__ . "/tmp/serialized.dat";
		$this->assertEquals(true,file_exists($filename));

		$serialized = Files::GetFileContent($filename);
		$tt = unserialize($serialized);

		$this->assertEquals(1234,$tt->getId());
	}
}

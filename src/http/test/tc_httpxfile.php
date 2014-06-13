<?php
class tc_httpxfile extends tc_base{
	function test(){
		global $HTTP_REQUEST;
		$HTTP_REQUEST = new HTTPRequest(); // reset

		$request = $HTTP_REQUEST;

		$this->assertNull(HTTPXFile::GetInstance());

		$request->setMethod("post");

		$this->assertNull(HTTPXFile::GetInstance());

		$request->setHeader("Content-Disposition",'attachment; filename="hlava.jpg"');

		$this->assertNotNull($file = HTTPXFile::GetInstance());
		$this->assertEquals("hlava.jpg",$file->getFileName());
		$this->assertEquals(false,$file->chunkedUpload());

		$request->setHeader("Content-Range","bytes 0-100/256");

		$this->assertEquals(true,$file->chunkedUpload());
		$this->assertEquals(true,$file->firstChunk());
		$this->assertEquals(false,$file->lastChunk());

		$request->setHeader("Content-Range","bytes 200-255/256");
		$this->assertEquals(true,$file->chunkedUpload());
		$this->assertEquals(false,$file->firstChunk());
		$this->assertEquals(true,$file->lastChunk());

		$request->setHeader("Content-Range","bytes 100-200/256");
		$this->assertEquals(true,$file->chunkedUpload());
		$this->assertEquals(false,$file->firstChunk());
		$this->assertEquals(false,$file->lastChunk());

		$request->setHeader("Content-Range","bytes 0-255/256");
		$this->assertEquals(false,$file->chunkedUpload());
		$this->assertEquals(true,$file->firstChunk());
		$this->assertEquals(true,$file->lastChunk());
	}

	function test_legacy_way(){
		global $HTTP_REQUEST,$_FILES;
		$HTTP_REQUEST = new HTTPRequest(); // reset

		$_FILES = null;

		$HTTP_REQUEST->setMethod("post");
		$HTTP_REQUEST->_HTTPRequest_headers = array("X-File-Name" => "hlava.jpg");
		$this->assertEquals("hlava.jpg",$HTTP_REQUEST->getHeader("x-file-name"));

		$GLOBALS["HTTP_RAW_POST_DATA"] = Files::GetFileContent("hlava.jpg",$err,$err_str);
		$this->assertTrue(strlen($HTTP_REQUEST->getRawPostData())>0);

		$xfile = HTTPXFile::GetInstance(array("name" => "file.jpg"));
		$this->assertTrue(is_object($xfile));
		$this->assertFalse($xfile->chunkedUpload());
		$xfile->cleanUp();

		$file = $HTTP_REQUEST->getUploadedFile("file");
		$this->assertTrue(is_object($file));

		$this->assertTrue($file->isImage());
		$this->assertEquals(325,$file->getImageWidth());
		$this->assertEquals(448,$file->getImageHeight());

		$this->assertEquals("file",$file->getName());
		$this->assertEquals("hlava.jpg",$file->getFileName());

		$tmp_file = $file->getTmpFileName();
		$this->assertTrue(file_exists($tmp_file));
		$file->cleanUp();
		$this->assertFalse(file_exists($tmp_file));

		// cisteni
		$HTTP_REQUEST->setMethod("get");
		$HTTP_RAW_POST_DATA = null;
		$HTTP_REQUEST->_HTTPRequest_headers = array();
		$this->assertNull(HTTPXFile::GetInstance(array("name" => "file.jpg")));
	}
}

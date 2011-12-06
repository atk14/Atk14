<?
class tc_httpxfile extends tc_base{
	function test(){
		global $HTTP_REQUEST;

		$HTTP_REQUEST->setMethod("post");
		$HTTP_REQUEST->_HTTPRequest_headers = array("X-File-Name" => "hlava.jpg");
		$this->assertEquals("hlava.jpg",$HTTP_REQUEST->getHeader("x-file-name"));

		$GLOBALS["HTTP_RAW_POST_DATA"] = files::get_file_content("hlava.jpg",$err,$err_str);
		$this->assertTrue(strlen($HTTP_REQUEST->getRawPostData())>0);

		$xfile = HTTPXFile::GetInstance(array("name" => "file.jpg"));
		$this->assertTrue(is_object($xfile));
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
	}
}

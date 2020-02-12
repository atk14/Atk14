<?php
class tc_httpxfile extends tc_base{

	function test(){
		global $HTTP_REQUEST;
		$HTTP_REQUEST = new HTTPRequest(); // reset
		$request = $HTTP_REQUEST;

		$this->assertNull(HTTPXFile::GetInstance());

		$request->setMethod("post");

		$this->assertNull(HTTPXFile::GetInstance());

		$request->setHeader("Content-Disposition",sprintf('attachment; filename="%s"',rawurlencode("hlavička.jpg")));
		$request->setRawPostData(Files::GetFileContent(__DIR__ . "/hlava.jpg"));

		$this->assertNotNull($file = HTTPXFile::GetInstance());
		$this->assertEquals("hlavicka.jpg",$file->getFileName());
		$this->assertEquals("hlavička.jpg",$file->getFileName(array("sanitize" => false)));
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

	function test__destruct(){
		global $HTTP_REQUEST;
		$HTTP_REQUEST = new HTTPRequest(); // reset
		$request = $HTTP_REQUEST;
		$request->setMethod("post");
		$request->setHeader("Content-Disposition",'attachment; filename="condor.jpg"');
		$request->setRawPostData(Files::GetFileContent(__DIR__ . "/hlava.jpg"));

		$this->assertNotNull($file = HTTPXFile::GetInstance());

		$tmp_filename = $file->getTmpFilename();
		$this->assertTrue(file_exists($tmp_filename));

		unset($file);

		$this->assertFalse(file_exists($tmp_filename)); // the temporary file was automatically deleted in __destruct()

		//

		$this->assertNotNull($file2 = HTTPXFile::GetInstance());

		$tmp_filename2 = $file2->getTmpFilename();
		$this->assertTrue(file_exists($tmp_filename2));

		$this->assertNotEquals($tmp_filename,$tmp_filename2);

		$tmp_filename3 = $file2->moveToTemp();

		$this->assertNotEquals($tmp_filename2,$tmp_filename3);

		$this->assertFalse(file_exists($tmp_filename2));
		$this->assertTrue(file_exists($tmp_filename3));

		unset($file2);

		$this->assertTrue(file_exists($tmp_filename3)); // the moved temporary file was NOT automatically deleted in __destruct()
	}

	function test_getToken(){
		$req1 = new HTTPRequest();
		$req1->setMethod("post");
		$req1->setRemoteAddr("10.20.30.40");
		$req1->setHeader("Content-Disposition",'attachment; filename="hlava.jpg"');
		$req1->setHeader("Content-Range","bytes 0-100/256");
		$req1->setRawPostData(Files::GetFileContent(__DIR__ . "/hlava.jpg"));

		$req2 = new HTTPRequest();
		$req2->setMethod("post");
		$req2->setRemoteAddr("10.20.30.40");
		$req2->setHeader("Content-Disposition",'attachment; filename="hlava.jpg"');
		$req2->setHeader("Content-Range","bytes 200-255/256");
		$req2->setRawPostData(Files::GetFileContent(__DIR__ . "/hlava.jpg"));

		$file1 = HTTPXFile::GetInstance(array("request" => $req1));
		$file2 = HTTPXFile::GetInstance(array("request" => $req2));

		$this->assertEquals(32,strlen($file1->getToken()));
		$this->assertTrue($file1->getToken()==$file2->getToken());

		$req2->setHeader("Content-Range","bytes 200-255/3000"); // different total size
		$this->assertTrue($file1->getToken()!=$file2->getToken());

		$req2->setHeader("Content-Range","bytes 200-255/256");
		$this->assertTrue($file1->getToken()==$file2->getToken());

		$req2->setHeader("Content-Disposition",'attachment; filename="hlava.png"'); // different filename
		$file2 = HTTPXFile::GetInstance(array("request" => $req2));
		$this->assertTrue($file1->getToken()!=$file2->getToken());

		$req2->setHeader("Content-Disposition",'attachment; filename="hlava.jpg"');
		$file2 = HTTPXFile::GetInstance(array("request" => $req2));
		$this->assertTrue($file1->getToken()==$file2->getToken());

		$req2->setRemoteAddr("11.22.33.44"); // different remote address
		$this->assertTrue($file1->getToken()!=$file2->getToken());
		$this->assertTrue($file1->getToken(array("consider_remote_addr" => false))==$file2->getToken(array("consider_remote_addr" => false)));

		$req2->setRemoteAddr("10.20.30.40");
		$this->assertTrue($file1->getToken()==$file2->getToken());
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

	function test_content_length(){
		global $HTTP_REQUEST;
		$HTTP_REQUEST = new HTTPRequest(); // reset
		$request = $HTTP_REQUEST;

		$content = Files::GetFileContent(__DIR__ . "/hlava.jpg");

		$request->setMethod("post");
		$request->setHeader("Content-Disposition",'attachment; filename="condor.jpg"');
		$request->setRawPostData($content);

		// without Content-Length header
		$this->assertNotNull($file = HTTPXFile::GetInstance());

		// proper Content-Length header
		$request->setHeader("Content-Length",strlen($content));
		$this->assertNotNull($file = HTTPXFile::GetInstance());

		// improper Content-Length header
		$request->setHeader("Content-Length",strlen($content)-1);
		$this->assertNull($file = HTTPXFile::GetInstance());

		// negative value in Content-Length header
		$request->setHeader("Content-Length",-123);
		$this->assertNull($file = HTTPXFile::GetInstance());
	}

	function test_empty_file(){
		global $HTTP_REQUEST;
		$HTTP_REQUEST = new HTTPRequest(); // reset
		$request = $HTTP_REQUEST;

		$request->setMethod("post");
		$request->setHeader("Content-Disposition",'attachment; filename="condor.jpg"');
		$request->setRawPostData("");

		// without Content-Length header
		$this->assertNull($file = HTTPXFile::GetInstance());

		// proper Content-Length header
		$request->setHeader("Content-Length",0);
		$this->assertNotNull($file = HTTPXFile::GetInstance());

		// improper Content-Length header
		$request->setHeader("Content-Length",1);
		$this->assertNull($file = HTTPXFile::GetInstance());

		// negative value in Content-Length header
		$request->setHeader("Content-Length",-1);
		$this->assertNull($file = HTTPXFile::GetInstance());
	}
}

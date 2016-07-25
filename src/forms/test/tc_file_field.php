<?php
class TcFileField extends TcBase{
	function test(){
		global $_FILES,$_POST;
		$image = $this->_get_uploaded_jpeg();

		$field = new FileField(array("required" => false));
		ini_set("upload_max_filesize","2M");
		$_POST["MAX_FILE_SIZE"] = 1024 * 1024;

		list($err,$value) = $field->clean(null);
		$this->assertEquals(null,$value);
		$this->assertEquals(null,$err);

		list($err,$value) = $field->clean($image);
		$this->assertTrue(is_a($value,"HTTPUploadedFile"));
		$this->assertEquals("hlava.jpg",$value->getFileName());
		$this->assertEquals(null,$err);

		list($err,$value) = $field->clean(UPLOAD_ERR_INI_SIZE);
		$this->assertEquals(null,$value);
		$this->assertEquals("The uploaded file exceeds the max file size value that is set on the server (2M)",$err);

		list($err,$value) = $field->clean(UPLOAD_ERR_FORM_SIZE);
		$this->assertEquals(null,$value);
		$this->assertEquals("The uploaded file exceeds the max file size directive that was specified in the HTML form (1048576)",$err);

		list($err,$value) = $field->clean(99);
		$this->assertEquals(null,$value);
		$this->assertEquals("An error occurred during file upload",$err);
	}

	function test_allowed_mime_types(){
		$image = $this->_get_uploaded_jpeg();
		$this->assertEquals("image/jpeg",$image->getMimeType());

		$field = new FileField(array("allowed_mime_types" => array()));
		list($err,$value) = $field->clean($image);
		$this->assertEquals(null,$err);
		$this->assertTrue(!!$value);

		$field = new FileField(array("allowed_mime_types" => array("image/jpeg")));
		list($err,$value) = $field->clean($image);
		$this->assertEquals(null,$err);
		$this->assertTrue(!!$value);

		$field = new FileField(array("allowed_mime_types" => array("image/jpeg","application/pdf")));
		list($err,$value) = $field->clean($image);
		$this->assertEquals(null,$err);
		$this->assertTrue(!!$value);

		$field = new FileField(array("allowed_mime_types" => array("application/pdf")));
		list($err,$value) = $field->clean($image);
		$this->assertEquals("Unsupported file type: image/jpeg",$err);
		$this->assertEquals(null,$value);

		$field = new FileField(array("allowed_mime_types" => array("application/pdf","/^image\/.*/")));
		list($err,$value) = $field->clean($image);
		$this->assertEquals(null,$err);
		$this->assertTrue(!!$value);
	}

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

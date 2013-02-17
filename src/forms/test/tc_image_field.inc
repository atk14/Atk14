<?php
class tc_image_field extends tc_base{
	function test_file_formats(){
		$image = $this->_get_uploaded_jpeg();

		$field = new ImageField(array());
		list($err,$value) = $field->clean($image);
		$this->assertNotNull($value);
		$this->assertNull($err);

		$field = new ImageField(array(
			"file_formats" => array("png","jpeg")
		));
		list($err,$value) = $field->clean($image);
		$this->assertNotNull($value);
		$this->assertNull($err);

		$field = new ImageField(array(
			"file_formats" => array("png")
		));
		list($err,$value) = $field->clean($image);
		$this->assertNull($value);
		$this->assertEquals($field->messages["file_formats"],$err);
	}

	function _get_uploaded_jpeg(){
		return HTTPUploadedFile::GetInstance(array(
			"tmp_name" => dirname(__FILE__)."/../../http/test/hlava.jpg", // just borrowing a testing image :)
			"name" => "hlava.jpg",
		),
		"image",
		array("testing_mode" => true));
	}
}

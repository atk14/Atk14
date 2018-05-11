<?php
class TcImageField extends TcBase{
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
		$this->assertEquals(strtr($field->messages["file_formats"],array("%mime_type%" => "image/jpeg")),$err);
	}
}

<?
class tc_files extends tc_base{
	function test_get_file_content(){
		$content = files::get_file_content("test.txt",$err,$err_str);
		$this->assertFalse($err);
		$this->assertEquals("Hello from the Earth!\n",$content); // nechapu ten \n

		$content = files::get_file_content("empty_file.txt",$err,$err_str);
		$this->assertFalse($err);
		$this->assertEquals("",$content);

		$content = files::get_file_content("non_existing_file.txt",$err,$err_str);
		$this->assertTrue($err);
		$this->assertEquals("non_existing_file.txt is not a file",$err_str);

	}
	function test_get_image_size(){
		$hlava = files::get_file_content("hlava.jpg",$err,$err_str);
		$this->assertEquals(68423,strlen($hlava));
		list($width,$height) = files::get_image_size($hlava,$err,$err_str);
		$this->assertEquals(325,$width);
		$this->assertEquals(448,$height);

		$hlava = "xxxxxxxxxxxxxxxxx";
		$this->assertNull(files::get_image_size($hlava,$err,$err_str));
	}
}
?>

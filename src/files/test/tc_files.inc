<?php
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

	function test_deterine_file_type(){
		$this->assertEquals("image/jpeg",files::determine_file_type("hlava.jpg"));
		$this->assertEquals("text/plain",files::determine_file_type("test.txt"));
	}

	function test_write_to_temp(){
		$content = files::get_file_content("hlava.jpg");
		$tmp_filename = files::write_to_temp($content);
		$this->assertTrue(file_exists($tmp_filename));
		$this->assertNotContains("hlava.jpg",$tmp_filename);
		$tmp_content = files::get_file_content($tmp_filename);
		$this->assertEquals($content,$tmp_content);

		$tmp_filename2 = files::write_to_temp($content);
		$this->assertTrue(file_exists($tmp_filename2));
		$this->assertTrue($tmp_filename!=$tmp_filename2);

		files::unlink($tmp_filename);
		files::unlink($tmp_filename2);
	}
}

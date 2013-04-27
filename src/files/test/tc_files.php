<?php
class TcFiles extends TcBase{
	function test_get_file_content(){
		$content = Files::GetFileContent("test.txt",$err,$err_str);
		$this->assertFalse($err);
		$this->assertEquals("Hello from the Earth!\n",$content); // nechapu ten \n

		$content = Files::GetFileContent("empty_file.txt",$err,$err_str);
		$this->assertFalse($err);
		$this->assertEquals("",$content);

		$content = Files::GetFileContent("non_existing_file.txt",$err,$err_str);
		$this->assertTrue($err);
		$this->assertEquals("non_existing_file.txt is not a file",$err_str);

	}

	function test_get_image_size(){
		$hlava = Files::GetFileContent("hlava.jpg",$err,$err_str);
		$this->assertEquals(68423,strlen($hlava));
		list($width,$height) = Files::GetImageSize($hlava,$err,$err_str);
		$this->assertEquals(325,$width);
		$this->assertEquals(448,$height);

		$hlava = "xxxxxxxxxxxxxxxxx";
		$this->assertNull(Files::GetImageSize($hlava,$err,$err_str));
	}

	function test_deterine_file_type(){
		$this->assertEquals("image/jpeg",Files::DetermineFileType("hlava.jpg"));
		$this->assertEquals("text/plain",Files::DetermineFileType("test.txt"));
	}

	function test_write_to_temp(){
		$content = Files::GetFileContent("hlava.jpg");
		$tmp_filename = Files::WriteToTemp($content);
		$this->assertTrue(file_exists($tmp_filename));
		$this->assertNotContains("hlava.jpg",$tmp_filename);
		$tmp_content = Files::GetFileContent($tmp_filename);
		$this->assertEquals($content,$tmp_content);

		$tmp_filename2 = Files::WriteToTemp($content);
		$this->assertTrue(file_exists($tmp_filename2));
		$this->assertTrue($tmp_filename!=$tmp_filename2);

		Files::Unlink($tmp_filename);
		Files::Unlink($tmp_filename2);
	}
}

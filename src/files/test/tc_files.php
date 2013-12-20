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
		$this->assertEquals("93724a4b921ddaf8582bbcb7f2077034",md5($hlava));
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

	function test_move_file(){
		$dir1 = TEMP."/dir_1/";
		$dir2 = TEMP."/dir_2";
		$dir3 = TEMP."/dir_3";

		if(file_exists($dir1)){ rmdir($dir1); }
		if(file_exists($dir2)){ rmdir($dir2); }
		if(file_exists($dir3)){ rmdir($dir3); }

		mkdir($dir1);
		mkdir($dir3);
		
		$this->assertEquals(true,file_exists($dir1));
		$this->assertEquals(false,file_exists($dir2));

		Files::MoveFile($dir1,$dir2);

		$this->assertEquals(false,file_exists($dir1));
		$this->assertEquals(true,file_exists($dir2) && is_dir($dir2));

		touch("$dir2/a_file.txt");

		$this->assertEquals(true,file_exists("$dir2/a_file.txt"));
		$this->assertEquals(false,file_exists("$dir2/another_file.txt"));
		
		Files::MoveFile("$dir2/a_file.txt","$dir2/another_file.txt");

		$this->assertEquals(false,file_exists("$dir2/a_file.txt"));
		$this->assertEquals(true,file_exists("$dir2/another_file.txt"));

		// moving from a directory to another directory
		Files::MoveFile("$dir2/another_file.txt","$dir3");

		$this->assertEquals(false,file_exists("$dir2/another_file.txt"));
		$this->assertEquals(true,file_exists("$dir3/another_file.txt"));

		unlink("$dir3/another_file.txt");

		// moving directory
		Files::MoveFile("$dir3","$dir2/");

		$this->assertEquals(false,file_exists($dir3));
		$this->assertEquals(true,file_exists("$dir2/dir_3/"));

		rmdir("$dir2/dir_3");
	}
}

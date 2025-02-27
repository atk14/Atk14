<?php
class TcFiles extends TcBase{

	function test_get_file_content(){
		$content = Files::GetFileContent("test.txt",$err,$err_str);
		$this->assertFalse($err);
		$this->assertEquals("Hello from the Earth!\n",$content); // nechapu ten \n

		$content = Files::GetFileContent("empty_file.txt",$err,$err_str);
		$this->assertFalse($err);
		$this->assertTrue($content === "");

		$content = Files::GetFileContent("non_existing_file.txt",$err,$err_str);
		$this->assertTrue($err);
		$this->assertEquals("non_existing_file.txt is not a file",$err_str);
		$this->assertTrue($content === null);

	}

	function test_get_image_size(){
		list($width,$height) = Files::GetImageSize("hlava.jpg",$err,$err_str);
		$this->assertEquals(325,$width);
		$this->assertEquals(448,$height);

		list($width,$height) = Files::GetImageSize("sample_files/sample.webp",$err,$err_str);
		if(PHP_VERSION_ID>=70100){
			// getimagesize() CHANGELOG: 7.1.0 Added WebP support.
			$this->assertEquals(50,$width);
			$this->assertEquals(38,$height);
		}else{
			$this->assertEquals(null,$width);
			$this->assertEquals(null,$height);
		}

		$hlava = Files::GetFileContent("hlava.jpg",$err,$err_str);
		$this->assertEquals(26130,strlen($hlava));
		$this->assertEquals("cf5486d582909df788be944049402003",md5($hlava));
		list($width,$height) = Files::GetImageSizeByContent($hlava,$err,$err_str);
		$this->assertEquals(325,$width);
		$this->assertEquals(448,$height);

		$image = Files::GetFileContent("sample_files/sample.webp",$err,$err_str);
		$this->assertEquals(2358,strlen($image));
		$this->assertEquals("c417b6553db97185609e3dc5925d8a42",md5($image));
		list($width,$height) = Files::GetImageSizeByContent($image,$err,$err_str);
		if(PHP_VERSION_ID>=70100){
			// getimagesize() CHANGELOG: 7.1.0 Added WebP support.
			$this->assertEquals(50,$width);
			$this->assertEquals(38,$height);
		}else{
			$this->assertEquals(null,$width);
			$this->assertEquals(null,$height);
		}

		$hlava = "xxxxxxxxxxxxxxxxx";
		$this->assertNull(Files::GetImageSizeByContent($hlava,$err,$err_str));

		// Legacy usage

		$hlava = Files::GetFileContent("hlava.jpg",$err,$err_str);
		$this->assertEquals(26130,strlen($hlava));
		$this->assertEquals("cf5486d582909df788be944049402003",md5($hlava));
		list($width,$height) = Files::GetImageSize($hlava,$err,$err_str);
		$this->assertEquals(325,$width);
		$this->assertEquals(448,$height);
	}

	function test_deterine_file_type(){
		$this->assertEquals("image/jpeg",Files::DetermineFileType("hlava.jpg"));
		$this->assertEquals("text/plain",Files::DetermineFileType("test.txt"));

		$this->assertEquals("image/jpeg",Files::DetermineFileType("hlava.jpg",array(),$preferred_suffix));
		$this->assertEquals("jpg",$preferred_suffix);
		$this->assertEquals("text/plain",Files::DetermineFileType("test.txt",array(),$preferred_suffix));
		$this->assertEquals("txt",$preferred_suffix);

		$tmp_file = Files::CopyToTemp("hlava.jpg");
		$this->assertEquals("image/jpeg",Files::DetermineFileType($tmp_file,array(),$preferred_suffix));
		$this->assertEquals("jpg",$preferred_suffix);
		$this->assertEquals("image/jpeg",Files::DetermineFileType($tmp_file,array("original_filename" => "image.bmp")));
		$this->assertEquals("jpg",$preferred_suffix);
	}

	function test_write_to_temp(){
		$content = Files::GetFileContent("hlava.jpg");
		$tmp_filename = Files::WriteToTemp($content);
		$this->assertTrue(file_exists($tmp_filename));
		$this->assertStringNotContains("hlava.jpg",$tmp_filename);
		$tmp_content = Files::GetFileContent($tmp_filename);
		$this->assertEquals($content,$tmp_content);

		$tmp_filename2 = Files::WriteToTemp($content);
		$this->assertTrue(file_exists($tmp_filename2));
		$this->assertTrue($tmp_filename!=$tmp_filename2);

		Files::Unlink($tmp_filename);
		Files::Unlink($tmp_filename2);
	}

	function test_get_temp_dir(){
		$tmp = Files::GetTempDir();

		$this->assertEquals(TEMP,$tmp);
	}

	function test_get_temp_filename(){
		$t1 = Files::GetTempFilename();
		$t2 = Files::GetTempFilename();
		$t3 = Files::GetTempFilename("pdf_creator_");

		$this->assertStringContains(TEMP,$t1);
		$this->assertStringContains(TEMP,$t2);
		$this->assertStringContains(TEMP,$t3);

		$this->assertStringContains("files_tmp_",$t1); // default prefix
		$this->assertStringContains("files_tmp_",$t2);
		$this->assertStringNotContains("files_tmp_",$t3);
		$this->assertStringContains("pdf_creator_",$t3);

		$this->assertTrue($t1!=$t2);

		// prefix sanitization
		$t = Files::GetTempFilename("bad/joke");

		$this->assertStringNotContains("bad/joke",$t);
		$this->assertStringContains("bad_joke",$t);
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

	function test_AppendToFile(){
		$filename = Files::GetTempFilename();
		$this->assertEquals(false,file_exists($filename));

		$this->assertEquals(5,Files::WriteToFile($filename,"Hello"));
		$this->assertEquals("Hello",Files::GetFileContent($filename));

		$this->assertEquals(7,Files::AppendToFile($filename," World!"));
		clearstatcache();
		$this->assertEquals("Hello World!",Files::GetFileContent($filename));

		Files::Unlink($filename);
	}

	function test_TouchFile(){
		$filename = Files::GetTempFilename();
		$this->assertEquals(false,file_exists($filename));

		Files::TouchFile($filename,$err,$err_str);
		clearstatcache();
		$this->assertEquals(false,$err);
		$this->assertEquals("",$err_str);
		$this->assertEquals(true,file_exists($filename));
		$this->assertEquals(0,filesize($filename));

		Files::AppendToFile($filename,"test");
		clearstatcache();
		$this->assertEquals(4,filesize($filename));
		$filemtime = filemtime($filename);
		$fileatime = fileatime($filename);

		usleep(1100000); // 1.1 sec

		Files::TouchFile($filename,$err,$err_str);
		clearstatcache();
		$this->assertEquals(false,$err);
		$this->assertEquals("",$err_str);
		$this->assertEquals(true,file_exists($filename));
		$this->assertEquals(4,filesize($filename));
		$filemtime2 = filemtime($filename);
		$fileatime2 = fileatime($filename);

		$this->assertTrue($filemtime2>$filemtime);
		$this->assertTrue($fileatime2>$fileatime);
	}

	function test_EmptyFile(){
		$filename = Files::GetTempFilename();
		$this->assertEquals(false,file_exists($filename));

		Files::WriteToFile($filename,"test");
		clearstatcache();
		$this->assertEquals(true,file_exists($filename));
		$this->assertEquals(4,filesize($filename));

		$ret = Files::EmptyFile($filename,$err,$err_str);
		clearstatcache();
		$this->assertEquals(true,$ret);
		$this->assertEquals(false,$err);
		$this->assertEquals("",$err_str);
		$this->assertEquals(true,file_exists($filename));
		$this->assertEquals(0,filesize($filename));
		$filemtime = filemtime($filename);
		$fileatime = fileatime($filename);

		usleep(1100000); // 1.1 sec

		$ret = Files::EmptyFile($filename,$err,$err_str);
		clearstatcache();
		$this->assertEquals(true,$ret);
		$this->assertEquals(false,$err);
		$this->assertEquals("",$err_str);
		$this->assertEquals(true,file_exists($filename));
		$this->assertEquals(0,filesize($filename));
		$filemtime2 = filemtime($filename);
		$fileatime2 = fileatime($filename);

		$this->assertTrue($filemtime2>$filemtime);
		$this->assertTrue($fileatime2>$fileatime);
	}

	function test__NormalizeFilename(){
		foreach(array(
			"/path/to/file" => "/path/to/file",
			"path/to/dir/" => "path/to/dir/",
			"../path/to/file" => "../path/to/file",
			"/path/to/file" => "/path/to/file",
			"/../path/to/file" => "/../path/to/file", // in fact this is a nonsense

			"////path///to//file" => "/path/to/file",
			"////path///to//dir///" => "/path/to/dir/",
			"/path/to//../tmp/images/..//attachments/" => "/path/tmp/attachments/",
			"/path/to//..///../tmp/images/..//attachments/" => "/tmp/attachments/",
		) as $filename => $normalized){
			$this->assertEquals($normalized,Files::_NormalizeFilename($filename));
		}
	}

	function test_Mkdir(){
		if(file_exists($_d = TEMP . "/ddd/d3")){ rmdir($_d); }
		if(file_exists($_d = TEMP . "/ddd")){ rmdir($_d); }

		$dir = TEMP . "/ddd/../ddd/d3";
		$this->assertFalse(file_exists($dir));

		$this->assertEquals(1,Files::Mkdir($dir)); // in fact 2 should be returned (2 directories were created)
		$this->assertEquals(0,Files::Mkdir($dir));
		$this->assertTrue(file_exists($dir));

		rmdir(TEMP."/ddd/d3");
		rmdir(TEMP."/ddd");
	}

	function test_MkdirForFile(){
		$directory = TEMP . "/dir_for_a_file/";
		$filename = "$directory/data.txt";
		if(file_exists($directory)){ rmdir($directory); }

		$this->assertFalse(file_exists($directory));

		$out = Files::MkdirForFile($filename,$error);
		$this->assertEquals(1,$out);
		$this->assertEquals(false,$error);

		$this->assertTrue(file_exists($directory));

		$out = Files::MkdirForFile($filename,$error);
		$this->assertEquals(0,$out);
		$this->assertEquals(false,$error);
	}

	function test_DetermineFileType(){

		// http://en.wikipedia.org/wiki/Internet_media_type

		$this->assertEquals(null,Files::DetermineFileType("non_existing_file.dat"));
		
		foreach(array(
			"jpg" => "image/jpeg",
			"png" => "image/png",
			"gif" => "image/gif",
			"tiff" => "image/tiff",
			"bmp" => "image/bmp",
			"webp" => "image/webp",
			"avif" => "image/avif",

			"eps" => "application/postscript",
			"ai" => "application/postscript",
			"svg" => "image/svg+xml",

			"odt" => "application/vnd.oasis.opendocument.text",
			"ods" => "application/vnd.oasis.opendocument.spreadsheet",

			"xls" => "application/vnd.ms-excel",
			"doc" => "application/msword",
			"ppt" => "application/vnd.ms-powerpoint",
			"xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",

			"csv" => "text/csv",

			"zip" => "application/zip",

			"docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",

			"mp3" => "audio/mpeg",
 		) as $file => $mime_type){
			$file = __DIR__."/sample_files/sample.$file";
			$this->assertEquals($mime_type,Files::DetermineFileType($file),$file);
		}

		foreach(array(
			array(
				"urls" => array("https://github.com/projectivetech/media-samples/raw/master/sample.mov"),
				"mime_types" => array("video/quicktime"),
			),
			array(
				"urls" => array("https://github.com/projectivetech/media-samples/raw/master/sample.wmv"),
				"mime_types" => array("video/x-ms-asf"),
			),
			array(
				"urls" => array("https://github.com/projectivetech/media-samples/raw/master/sample.avi"),
				"mime_types" => array("video/x-msvideo"),
			),
			array(
				"urls" => array("https://github.com/projectivetech/media-samples/raw/master/sample.mp4"),
				"mime_types" => array("video/mp4"),
			),
			array(
				"urls" => array("https://filesamples.com/samples/video/mkv/sample_960x540.mkv"),
				"mime_types" => array("video/x-matroska"),
			),
			array(
				"urls" => array(
					"https://github.com/appium-boneyard/sample-code/blob/master/sample-code/apps/ContactManager/ContactManager.apk?raw=true",
					"https://github.com/katalon-studio-samples/android-mobile-tests/blob/master/androidapp/APIDemos.apk?raw=true",
					"https://github.com/appium/sample-apps/blob/master/pre-built/selendroid-test-app.apk?raw=true"
				),
				"mime_types" => array("application/vnd.android.package-archive"),
			),
			array(
				"urls" => array("https://github.com/MeetMe/AppRate/blob/master/AppRateDownloads/AppRate_0.8.jar?raw=true"),
				"mime_types" => array("application/java-archive"),
			)
		) as $item){
			$urls = $item["urls"];
			$mime_types = $item["mime_types"];
			foreach($urls as $url){
				$filename = $url;
				$filename = preg_replace('/\?.*$/','',$filename);
				$filename = preg_replace('/^.*\//','',$filename);
				$uf = new UrlFetcher($url);
				$this->assertTrue($uf->found(),"$url");
				$file = Files::WriteToTemp($uf->getContent());
				$mime_type = Files::DetermineFileType($file,array("original_filename" => $filename));
				$this->assertTrue(in_array($mime_type,$mime_types),"$url - $mime_type not in [".join(", ",$mime_types)."]");
				unlink($file);
			}
		}
	}

	function test_RecursiveUnlinkDir(){
		$this->assertEquals(false,file_exists(TEMP . "/to_be_deleted"));

		mkdir(TEMP . "/to_be_deleted");
		copy(__FILE__ , TEMP."/to_be_deleted/file1");
		copy(__FILE__ , TEMP."/to_be_deleted/file2");
		mkdir(TEMP . "/to_be_deleted/0");
		copy(__FILE__ , TEMP."/to_be_deleted/0/file3");

		$this->assertEquals(true,file_exists(TEMP."/to_be_deleted/file1"));
		$this->assertEquals(true,file_exists(TEMP."/to_be_deleted/file2"));

		$files_deleted = Files::RecursiveUnlinkDir(TEMP . "/to_be_deleted");
		$this->assertEquals(5,$files_deleted);
	}

	function test_FindFiles(){
		$files = Files::FindFiles("sample_files/");
		$this->assertTrue(sizeof($files)>10);
		$this->assertTrue(in_array('sample_files/sample.jpg',$files));

		// --- maxdepth

		$files = Files::FindFiles("sample_files/",array("maxdepth" => 0));
		$this->assertEquals(array(),$files);

		$files = Files::FindFiles("sample_files/",array("maxdepth" => -1));
		$this->assertEquals(array(),$files);

		$files = Files::FindFiles("sample_files/",array("maxdepth" => 1));
		$this->assertTrue(sizeof($files)>10);
		$this->assertTrue(in_array('sample_files/sample.jpg',$files));

		$files = Files::FindFiles("./");
		$files_maxdepth_limited = Files::FindFiles("./",array("maxdepth" => 1));
		$this->assertTrue(sizeof($files)>sizeof($files_maxdepth_limited));
		$this->assertTrue(in_array('./tc_files.php',$files));
		$this->assertTrue(in_array('./sample_files/sample.jpg',$files));
		$this->assertTrue(in_array('./tc_files.php',$files_maxdepth_limited));
		$this->assertFalse(in_array('./sample_files/sample.jpg',$files_maxdepth_limited));

		// --- pattern

		$files = Files::FindFiles("./sample_files/",array(
			"pattern" => '/^sample\.(p..|jpg)$/'
		));
		$this->assertEquals(array(
			'./sample_files/sample.jpg',
			'./sample_files/sample.pdf',
			'./sample_files/sample.png',
			'./sample_files/sample.ppt',
		),$files);

		// --- invert_pattern

		$files = Files::FindFiles("./sample_files/",array(
			"pattern" => '/^sample\.(p..|jpg)$/',
			"invert_pattern" => '/\.ppt/'
		));
		$this->assertEquals(array(
			'./sample_files/sample.jpg',
			'./sample_files/sample.pdf',
			'./sample_files/sample.png',
		),$files);

		// --- hidden files are included

		touch('temp/.hidden_file.txt');

		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.txt$/'
		));
		$this->assertEquals(array('temp/.hidden_file.txt'),$files);

		$files = Files::FindFiles("temp/");
		$this->assertTrue(in_array('temp/.hidden_file.txt',$files));

		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.txt$/',
			"invert_pattern" => '/^\./',
		));
		$this->assertEquals(array(),$files);
		
		unlink('temp/.hidden_file.txt');


		// ----

		touch('temp/application.log',time() - 60);

		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.log$/'
		));
		$this->assertEquals(array('temp/application.log'),$files);

		// min_mtime
		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.log$/',
			"min_mtime" => time() - 30
		));
		$this->assertEquals(array(),$files);
		//
		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.log$/',
			"min_mtime" => time() - 60
		));
		$this->assertEquals(array('temp/application.log'),$files);
		// 
		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.log$/',
			"min_mtime" => time() - 120
		));
		$this->assertEquals(array('temp/application.log'),$files);

		// max_mtime
		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.log$/',
			"max_mtime" => time() - 120
		));
		$this->assertEquals(array(),$files);
		//
		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.log$/',
			"max_mtime" => time() - 60
		));
		$this->assertEquals(array('temp/application.log'),$files);
		//
		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.log$/',
			"max_mtime" => time() - 30
		));
		$this->assertEquals(array('temp/application.log'),$files);

		// both min_mtime & max_mtime
		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.log$/',
			"min_mtime" => time() - 120,
			"max_mtime" => time() - 30
		));
		$this->assertEquals(array('temp/application.log'),$files);
		//
		$files = Files::FindFiles("temp/",array(
			"pattern" => '/^.*\.log$/',
			"min_mtime" => time() - 180,
			"max_mtime" => time() - 120
		));
		$this->assertEquals(array(),$files);

		unlink("temp/application.log");
	}

	function test_permissions(){

		// Directories

		Files::Mkdir(TEMP . "/d1");	
		$this->assertEquals("777",substr(decoct(fileperms(TEMP . "/d1")),-3));

		$prev_dir_perms = Files::SetDefaultDirPerms(0750);
		$this->assertEquals("777",decoct($prev_dir_perms));

		Files::Mkdir(TEMP . "/d2");	
		$this->assertEquals("750",substr(decoct(fileperms(TEMP . "/d2")),-3));

		// Files

		Files::WriteToFile(TEMP . "/d1/f1", "content");
		$this->assertEquals("666",substr(decoct(fileperms(TEMP . "/d1/f1")),-3));

		$prev_file_perms = Files::SetDefaultFilePerms(0640);
		$this->assertEquals("666",decoct($prev_file_perms));

		Files::WriteToFile(TEMP . "/d2/f2", "content");
		$this->assertEquals("640",substr(decoct(fileperms(TEMP . "/d2/f2")),-3));

		// Resetting perms

		$prev = Files::SetDefaultDirPerms($prev_dir_perms);
		$this->assertEquals("750",decoct($prev));

		$prev = Files::SetDefaultFilePerms($prev_file_perms);
		$this->assertEquals("640",decoct($prev));

		// Cleaning

		unlink(TEMP . "/d1/f1");
		rmdir(TEMP . "/d1");
		unlink(TEMP . "/d2/f2");
		rmdir(TEMP . "/d2");

		$this->assertEquals(false,file_exists(TEMP . "/d1"));
		$this->assertEquals(false,file_exists(TEMP . "/d2"));
	}

	function test_NormalizeFilePerms(){

		// Directory

		$dir = TEMP . "/d1";
		if(file_exists($dir)){ rmdir($dir); }

		Files::SetDefaultDirPerms(0755);
		Files::Mkdir($dir);
		//
		$this->assertEquals(true,Files::NormalizeFilePerms($dir));
		clearstatcache();
		$this->assertEquals("755",substr(decoct(fileperms($dir)),-3));
		//
		$this->assertEquals(true,Files::NormalizeFilePerms($dir));
		clearstatcache();
		$this->assertEquals("755",substr(decoct(fileperms($dir)),-3));
		//
		Files::SetDefaultDirPerms(0777);
		$this->assertEquals(true,Files::NormalizeFilePerms($dir));
		clearstatcache();
		$this->assertEquals("777",substr(decoct(fileperms($dir)),-3));

		// File

		$file = TEMP . "/f1";
		if(file_exists($file)){ unlink($file); }

		Files::SetDefaultFilePerms(0644);
		Files::WriteToFile($file,"content");
		//
		$this->assertEquals(true,Files::NormalizeFilePerms($file));
		clearstatcache();
		$this->assertEquals("644",substr(decoct(fileperms($file)),-3));
		//
		$this->assertEquals(true,Files::NormalizeFilePerms($file));
		clearstatcache();
		$this->assertEquals("644",substr(decoct(fileperms($file)),-3));
		//
		Files::SetDefaultFilePerms(0666);
		$this->assertEquals(true,Files::NormalizeFilePerms($file));
		clearstatcache();
		$this->assertEquals("666",substr(decoct(fileperms($file)),-3));

		// Cleaning

		rmdir($dir);
		unlink($file);
	}

	function test_WriteToCacheFile(){
		$ret = Files::WriteToCacheFile("temp/cache_file","Cache_Content",$err,$err_str);
		$this->assertFalse($err);
		$this->assertEquals(null,$err_str);
		$this->assertEquals(13,$ret);
		
		$this->assertTrue(file_exists("temp/cache_file"));
		$this->assertEquals("Cache_Content",Files::GetFileContent("temp/cache_file"));

		//

		$ret = @Files::WriteToCacheFile("non_existing_dir/cache_file","Cache_Content",$err,$err_str);
		$this->assertTrue($err);
		$this->assertStringContains("failed to open file for writing",$err_str);
		$this->assertEquals(0,$ret);

		$this->assertFalse(file_exists("non_existing_dir/cache_file"));

		// Cleaning

		unlink("temp/cache_file");
	}
}

<?php
class TcPacker extends TcBase{

	function test_php(){
		$this->assertTrue(function_exists("openssl_encrypt"));
		$this->assertTrue(function_exists("gzcompress"));
	}

	function test(){
		$ary = array("No", "Way", "Way", "Long", "Way", "Far");

		$simple = Packer::Pack($ary,$opts = array("use_compress" => false, "enable_encryption" => false, "use_json_serialization" => false));
		$this->_test_unpacking($simple,$ary,$opts,"packed_simple");

		$encrypt = Packer::Pack($ary,$opts = array("use_compress" => false, "enable_encryption" => true, "use_json_serialization" => false));
		$this->_test_unpacking($encrypt,$ary,$opts,"packed_encrypt");

		$compress = Packer::Pack($ary,$opts = array("use_compress" => true, "enable_encryption" => false, "use_json_serialization" => false));
		$this->_test_unpacking($compress,$ary,$opts,"packed_compress");

		$complex = Packer::Pack($ary,$opts = array("use_compress" => true, "enable_encryption" => true, "use_json_serialization" => false));
		$this->_test_unpacking($complex,$ary,$opts,"packed_complex");

		$this->assertTrue(strlen($simple)>strlen($compress));
		$this->assertTrue(strlen($encrypt)>strlen($simple));
		$this->assertTrue(strlen($complex)>strlen($compress));
	}

	function test_enable_encryption(){
		$text = "an_important_looking_message";

		$packed = Packer::Pack($text,array("enable_encryption" => false));
		$packed_and_encrypted = Packer::Pack($text,array("enable_encryption" => true));

		$this->assertTrue(Packer::Unpack($packed,$out,array("enable_encryption" => false)));
		$this->assertEquals($text,$out);

		$this->assertFalse(Packer::Unpack($packed,$out,array("enable_encryption" => true)));
		$this->assertNull($out);

		$this->assertTrue(Packer::Unpack($packed_and_encrypted,$out,array("enable_encryption" => true)));
		$this->assertEquals($text,$out);
	}

	function test_encryption_with_extra_salt(){
		$text = "another_important_message";

		$packed = Packer::Pack($text,["enable_encryption" => true]);
		$packed2 = Packer::Pack($text,["enable_encryption" => true, "extra_salt" => ""]);
		$packed3 = Packer::Pack($text,["enable_encryption" => true, "extra_salt" => "pass1"]);
		$packed4 = Packer::Pack($text,["enable_encryption" => true, "extra_salt" => "pass1"]);
		$packed5 = Packer::Pack($text,["enable_encryption" => true, "extra_salt" => "pass2"]);

		$this->assertEquals($packed,$packed2);
		$this->assertNotEquals($packed2,$packed3);
		$this->assertEquals($packed3,$packed4);
		$this->assertNotEquals($packed4,$packed5);
	
		$this->assertTrue(Packer::Unpack($packed,$val,["enable_encryption" => true]));
		$this->assertEquals($text,$val);
		$this->assertTrue(Packer::Unpack($packed2,$val2,["enable_encryption" => true, "extra_salt" => ""]));
		$this->assertEquals($text,$val2);
		$this->assertFalse(Packer::Unpack($packed3,$val3,["enable_encryption" => true]));
		$this->assertEquals(null,$val3);
		$this->assertFalse(Packer::Unpack($packed4,$val4,["enable_encryption" => true, "extra_salt" => ""]));
		$this->assertEquals(null,$val4);
		$this->assertFalse(Packer::Unpack($packed5,$val5,["enable_encryption" => true, "extra_salt" => ""]));
		$this->assertEquals(null,$val5);

		$this->assertTrue(Packer::Unpack($packed3,$val3,["enable_encryption" => true, "extra_salt" => "pass1"]));
		$this->assertEquals($text,$val3);
		$this->assertTrue(Packer::Unpack($packed4,$val4,["enable_encryption" => true, "extra_salt" => "pass1"]));
		$this->assertEquals($text,$val4);
		$this->assertTrue(Packer::Unpack($packed5,$val5,["enable_encryption" => true, "extra_salt" => "pass2"]));
		$this->assertEquals($text,$val5);
	}

	function test_use_json_serialization(){
		foreach(array(
			array("a" => "Hello", "b" => "World!"),
			true,
			false,
			123,
			1.234,
			null,
		) as $value){
			$packed_json = Packer::Pack($value,array("use_json_serialization" => true, "use_compress" => false, "enable_encryption" => false));
			$packed_serialize = Packer::Pack($value,array("use_json_serialization" => false, "use_compress" => false, "enable_encryption" => false));

			$this->assertNotEquals($packed_json,$packed_serialize);

			$this->assertEquals(true,Packer::Unpack($packed_json,$value_json,array("use_json_serialization" => true, "enable_encryption" => false)));
			$this->assertEquals(true,Packer::Unpack($packed_serialize,$value_selialize,array("use_json_serialization" => false, "enable_encryption" => false)));

			$this->assertEquals($value,$value_json);
			$this->assertEquals($value,$value_selialize);
		}
	}

	function _test_unpacking($packed,$var,$optioons,$message = ""){
		//echo $packed."\n";
		$this->assertEquals(true,Packer::Unpack($packed,$out,$optioons),$message);
		$this->assertEquals($var,$out,$message);

		$this->assertEquals(false,Packer::Unpack($packed."x",$out,$optioons),$message);
		$this->assertEquals(null,$out,$message);
	}
}

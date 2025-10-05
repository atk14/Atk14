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
		$this->assertEquals("an_important_looking_message",$out);

		$this->assertFalse(Packer::Unpack($packed,$out,array("enable_encryption" => true)));
		$this->assertNull($out);

		$this->assertTrue(Packer::Unpack($packed_and_encrypted,$out,array("enable_encryption" => true)));
		$this->assertEquals("an_important_looking_message",$out);
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

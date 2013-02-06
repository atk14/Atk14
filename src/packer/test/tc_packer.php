<?php
class TcPacker extends TcBase{
	function test_php(){
		$this->assertTrue(function_exists("mcrypt_encrypt"));
		$this->assertTrue(function_exists("gzcompress"));
	}

	function test(){
		$ary = array("No", "Way", "Way", "Long", "Way", "Far");

		$simple = Packer::Pack($ary,$opts = array("use_compress" => false, "enable_encryption" => false));
		$this->_test_unpacking($simple,$ary,$opts,"packed_simple");

		$encrypt = Packer::Pack($ary,$opts = array("use_compress" => false, "enable_encryption" => true));
		$this->_test_unpacking($encrypt,$ary,$opts,"packed_encrypt");

		$compress = Packer::Pack($ary,$opts = array("use_compress" => true, "enable_encryption" => false));
		$this->_test_unpacking($compress,$ary,$opts,"packed_compress");

		$complex = Packer::Pack($ary,$opts = array("use_compress" => true, "enable_encryption" => true));
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

	function _test_unpacking($packed,$var,$optioons,$message = ""){
		//echo $packed."\n";
		$this->assertEquals(true,Packer::Unpack($packed,$out,$optioons),$message);
		$this->assertEquals($var,$out,$message);

		$this->assertEquals(false,Packer::Unpack($packed."x",$out,$optioons),$message);
		$this->assertEquals(null,$out,$message);
	}
}

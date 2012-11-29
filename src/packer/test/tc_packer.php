<?php
class TcPacker extends TcBase{
	function test_php(){
		$this->assertTrue(function_exists("mcrypt_encrypt"));
		$this->assertTrue(function_exists("gzcompress"));
	}

	function test(){
		$ary = array("No", "Way", "Way", "Long", "Way", "Far");

		$simple = Packer::Pack($ary,array("use_compress" => false, "enable_encryption" => false));
		$this->_test_unpacking($simple,$ary,"packed_simple");

		$encrypt = Packer::Pack($ary,array("use_compress" => false, "enable_encryption" => true));
		$this->_test_unpacking($encrypt,$ary,"packed_encrypt");

		$compress = Packer::Pack($ary,array("use_compress" => true, "enable_encryption" => false));
		$this->_test_unpacking($compress,$ary,"packed_compress");

		$complex = Packer::Pack($ary,array("use_compress" => true, "enable_encryption" => true));
		$this->_test_unpacking($complex,$ary,"packed_complex");

		$this->assertTrue(strlen($simple)>strlen($compress));
		$this->assertTrue(strlen($encrypt)>strlen($simple));
		$this->assertTrue(strlen($complex)>strlen($compress));
	}

	function _test_unpacking($packed,$var,$message = ""){
		//echo $packed."\n";
		$this->assertEquals(true,Packer::Unpack($packed,$out),$message);
		$this->assertEquals($var,$out,$message);

		$this->assertEquals(false,Packer::Unpack($packed."x",$out),$message);
		$this->assertEquals(null,$out,$message);
	}
}

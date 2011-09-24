<?php
/**
* POZOR!!! soubor je v kodovani UTF-8
*/
class tc_translate extends tc_base{

	function test_translate_array(){
		$ar_utf8 = array(
			"klíč1" => "ěš",
			"klíč2" => "šč"
		);

		$ar_iso = translate::trans($ar_utf8,"UTF-8","ISO-8859-2");
		$this->assertType("array",$ar_iso);
		$this->assertEquals(2,sizeof($ar_iso));
		reset($ar_iso);
		while(list($_key,$_value) = each($ar_iso)){
			$this->assertTrue(translate::check_encoding($_key,"UTF-8")); // klice zustaly v UTF-8
			$this->assertFalse(translate::check_encoding($_value,"UTF-8")); // kdezto hodnoty uz musi byt prekodovane
			$this->assertEquals(2,strlen($_value));	
		}

		$ar_iso = translate::trans($ar_utf8,"UTF-8","ISO-8859-2",array("recode_array_keys" => true));
		$this->assertType("array",$ar_iso);
		$this->assertEquals(2,sizeof($ar_iso));
		reset($ar_iso);
		while(list($_key,$_value) = each($ar_iso)){
			$this->assertFalse(translate::check_encoding($_key,"UTF-8")); // v teto chvili uz jsou i klice prekodovane do latin 2
			$this->assertFalse(translate::check_encoding($_value,"UTF-8")); 
			$this->assertEquals(2,strlen($_value));	
		}
	}

	function test_length(){
		$str = "ahoj";
		$this->assertEquals(4,translate::length($str,"ASCII"));

		$str = "erb";
		$this->assertEquals(3,translate::length($str,"UTF-8"));

		$str = "čep";
		$this->assertEquals(3,translate::length($str,"UTF-8"));

		$str = "čáp";
		$this->assertEquals(3,translate::length($str,"UTF-8"));

		$str = "veš";
		$this->assertEquals(3,translate::length($str,"UTF-8"));

		$str = "věž";
		$this->assertEquals(3,translate::length($str,"UTF-8"));

		$str = "věž";
		$this->assertEquals(3,translate::length($str,"UTF-8"));

		$str = "šíř";
		$this->assertEquals(3,translate::length($str,"UTF-8"));

		$this->assertEquals(3,translate::length($str,"UTF-8"));
	}

	function test_check_encoding(){
		$text = "ascii sample text";

		$this->assertTrue(translate::check_encoding($text,"ascii"));
		$this->assertTrue(translate::check_encoding($text,"utf-8"));

		$text = array(
			"test",
			"ascii"
		);

		$this->assertTrue(translate::check_encoding($text,"ascii"));
		$this->assertTrue(translate::check_encoding($text,"utf-8"));

		$text = array(
			"key1" => "value1",
			"key2" => "value2",
		);
		$this->assertTrue(translate::check_encoding($text,"utf-8"));
		$text["key3"] = array("value3.1","value3.2");
		$this->assertTrue(translate::check_encoding($text,"utf-8"));
		$text["key4"] = array("value4.1","value4.2".chr(250)); //invalid UTF-8 sequence in array value
		$this->assertFalse(translate::check_encoding($text,"utf-8"));
		unset($text["key4"]);
		$this->assertTrue(translate::check_encoding($text,"utf-8"));
		$text["key4"] = array(array("key4.1".chr(250) => "value4.1.2")); //invalid UTF-8 sequence in array key
		$this->assertFalse(translate::check_encoding($text,"utf-8"));

		$text = chr(0xC3).chr(0xA2).chr(0x80).chr(0x93);
		$this->assertFalse(translate::check_encoding($text,"utf-8"));

		$text = chr(0xE2).chr(0x80).chr(0x93);
		$this->assertTrue(translate::check_encoding($text,"utf-8"));
	}

	function test_hacks(){
		$text = chr(0xE2).chr(0x80).chr(0x93);
		$this->assertTrue(translate::check_encoding($text,"utf-8"));

		$text = translate::trans($text,"utf-8","iso-8859-2");
		$this->assertTrue(translate::check_encoding($text,"iso-8859-2"));

		$text = translate::trans($text,"iso-8859-2","utf-8");
		$this->assertTrue(translate::check_encoding($text,"utf-8"));
	}

	function test_windows_1250_issues(){
		$out = translate::trans('"',"utf-8","windows-1250");
		$this->assertEquals('"',$out);

		$out = translate::trans('---',"utf-8","windows-1250");
		$this->assertEquals('---',$out);
	}

	function test_to_cp852(){
		$this->assertEquals("hello",translate::trans("hello","utf-8","cp852"));

		$out = translate::trans("ěščč","utf-8","cp852");
		$this->assertTrue(strlen($out)>0);
		$this->assertFalse(translate::check_encoding($out,"utf-8"));
	}

	function test_translate_array_with_integers(){
		$ar = array(
			"word" => "čepice",
			"int" => 13,
			"null" => null
		);
		$ar = translate::trans($ar,"UTF-8","ASCII");
		$this->assertEquals("cepice",$ar["word"]);

		$this->assertTrue(is_int($ar["int"]));
		$this->assertEquals(13,$ar["int"]);

		$this->assertNull($ar["null"]);

		// ----
		$ar = array(
			"parent" => array(
				"word" => "čepice",
				"int" => 13,
				"null" => null
			)
		);
		$ar = translate::trans($ar,"UTF-8","ASCII");
		$this->assertEquals("cepice",$ar["parent"]["word"]);

		$this->assertTrue(is_int($ar["parent"]["int"]));
		$this->assertEquals(13,$ar["parent"]["int"]);

		$this->assertNull($ar["parent"]["null"]);
		
	}

	function test_utf8_to_ascii(){
		$str = "Hello".chr(0xC2).chr(0xA0)."world";
		$this->assertEquals("Hello world",translate::trans($str,"UTF-8","ASCII"));
	}

}

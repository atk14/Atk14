<?php
/**
* POZOR!!! soubor je v kodovani UTF-8
*/
class TcTranslate extends TcBase{

	function test_translate_array(){
		$ar_utf8 = array(
			"klíč1" => "ěš",
			"klíč2" => "šč"
		);

		$ar_iso = Translate::Trans($ar_utf8,"UTF-8","ISO-8859-2");
		$this->assertTrue(is_array($ar_iso));
		$this->assertEquals(2,sizeof($ar_iso));
		foreach($ar_iso as $_key => $_value){
			$this->assertTrue(Translate::CheckEncoding($_key,"UTF-8")); // klice zustaly v UTF-8
			$this->assertFalse(Translate::CheckEncoding($_value,"UTF-8")); // kdezto hodnoty uz musi byt prekodovane
			$this->assertEquals(2,strlen($_value));	
		}

		$ar_iso = Translate::Trans($ar_utf8,"UTF-8","ISO-8859-2",array("recode_array_keys" => true));
		$this->assertTrue(is_array($ar_iso));
		$this->assertEquals(2,sizeof($ar_iso));
		foreach($ar_iso as $_key => $_value){
		  $this->assertFalse(Translate::CheckEncoding($_key,"UTF-8")); // v teto chvili uz jsou i klice prekodovane do latin 2
			$this->assertFalse(Translate::CheckEncoding($_value,"UTF-8")); 
			$this->assertEquals(2,strlen($_value));	
		}
	}

	function test_length(){
		$str = "ahoj";
		$this->assertEquals(4,Translate::Length($str,"ASCII"));

		$str = "erb";
		$this->assertEquals(3,Translate::Length($str,"UTF-8"));

		$str = "čep";
		$this->assertEquals(3,Translate::Length($str,"UTF-8"));

		$str = "čáp";
		$this->assertEquals(3,Translate::Length($str,"UTF-8"));

		$str = "veš";
		$this->assertEquals(3,Translate::Length($str,"UTF-8"));

		$str = "věž";
		$this->assertEquals(3,Translate::Length($str,"UTF-8"));

		$str = "věž";
		$this->assertEquals(3,Translate::Length($str,"UTF-8"));

		$str = "šíř";
		$this->assertEquals(3,Translate::Length($str,"UTF-8"));

		$this->assertEquals(3,Translate::Length($str,"UTF-8"));
	}

	function test_check_encoding(){
		$text = "ascii sample text";

		$this->assertTrue(Translate::CheckEncoding($text,"ascii"));
		$this->assertTrue(Translate::CheckEncoding($text,"utf-8"));

		$text = array(
			"test",
			"ascii"
		);

		$this->assertTrue(Translate::CheckEncoding($text,"ascii"));
		$this->assertTrue(Translate::CheckEncoding($text,"utf-8"));

		$text = array(
			"key1" => "value1",
			"key2" => "value2",
		);
		$this->assertTrue(Translate::CheckEncoding($text,"utf-8"));
		$text["key3"] = array("value3.1","value3.2");
		$this->assertTrue(Translate::CheckEncoding($text,"utf-8"));
		$text["key4"] = array("value4.1","value4.2".chr(250)); //invalid UTF-8 sequence in array value
		$this->assertFalse(Translate::CheckEncoding($text,"utf-8"));
		unset($text["key4"]);
		$this->assertTrue(Translate::CheckEncoding($text,"utf-8"));
		$text["key4"] = array(array("key4.1".chr(250) => "value4.1.2")); //invalid UTF-8 sequence in array key
		$this->assertFalse(Translate::CheckEncoding($text,"utf-8"));

		$text = chr(0xC3).chr(0xA2).chr(0x80).chr(0x93);
		$this->assertFalse(Translate::CheckEncoding($text,"utf-8"));

		$text = chr(0xE2).chr(0x80).chr(0x93);
		$this->assertTrue(Translate::CheckEncoding($text,"utf-8"));
	}

	function test_hacks(){
		$text = chr(0xE2).chr(0x80).chr(0x93);
		$this->assertTrue(Translate::CheckEncoding($text,"utf-8"));

		$text = Translate::Trans($text,"utf-8","iso-8859-2");
		$this->assertTrue(Translate::CheckEncoding($text,"iso-8859-2"));

		$text = Translate::Trans($text,"iso-8859-2","utf-8");
		$this->assertTrue(Translate::CheckEncoding($text,"utf-8"));
	}

	function test_windows_1250_issues(){
		$out = Translate::Trans('"',"utf-8","windows-1250");
		$this->assertEquals('"',$out);

		$out = Translate::Trans('---',"utf-8","windows-1250");
		$this->assertEquals('---',$out);

		$out = Translate::Trans(chr(0x80),"windows-1250","utf-8"); // Euro symbol
		$this->assertEquals(chr(0xE2).chr(0x82).chr(0xAC),$out);
		$out = Translate::Trans($out,"utf-8","windows-1250");
		$this->assertEquals(chr(0x80),$out);
	}

	function test_to_cp852(){
		$this->assertEquals("hello",Translate::Trans("hello","utf-8","cp852"));

		$out = Translate::Trans("ěščč","utf-8","cp852");
		$this->assertTrue(strlen($out)>0);
		$this->assertFalse(Translate::CheckEncoding($out,"utf-8"));
	}

	function test_translate_array_with_integers(){
		$ar = array(
			"word" => "čepice",
			"int" => 13,
			"null" => null
		);
		$ar = Translate::Trans($ar,"UTF-8","ASCII");
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
		$ar = Translate::Trans($ar,"UTF-8","ASCII");
		$this->assertEquals("cepice",$ar["parent"]["word"]);

		$this->assertTrue(is_int($ar["parent"]["int"]));
		$this->assertEquals(13,$ar["parent"]["int"]);

		$this->assertNull($ar["parent"]["null"]);
	}

	function test_lower_upper(){
		$this->assertEquals("HELLO",Translate::Upper("Hello"));
		$this->assertEquals("hello",Translate::Lower("hello"));

		$this->assertEquals("HELLO",Translate::Upper("Hello","ASCII"));
		$this->assertEquals("hello",Translate::Lower("hello","ASCII"));

		//

		$this->assertEquals("KŘEMÍLEK",Translate::Upper("Křemílek"));
		$this->assertEquals("křemílek",Translate::Lower("KřemÍLEK"));

		$this->assertEquals("KŘEMÍLEK",Translate::Upper("Křemílek","UTF-8"));
		$this->assertEquals("křemílek",Translate::Lower("KřemÍLEK","UTF-8"));

		$this->assertNotEquals("KŘEMÍLEK",Translate::Upper("Křemílek","ASCII"));
		$this->assertNotEquals("křemílek",Translate::Lower("KřemÍLEK","ASCII"));

		$this->assertEquals("KŘEMÍLEK",Translate::Upper("Křemílek","UTF-8"));
		$this->assertEquals("křemílek",Translate::Lower("KřemÍLEK","UTF-8"));

		$this->assertEquals("§ • (symbols)",Translate::Lower("§ • (Symbols)","UTF-8"));
		$this->assertEquals("§ • (SYMBOLS)",Translate::UPPER("§ • (Symbols)","UTF-8"));
	}

	function test_utf8_to_ascii(){
		$str = "Hello".chr(0xC2).chr(0xA0)."world český";
		$this->assertEquals("Hello world cesky",Translate::Trans($str,"UTF-8","ASCII"));

		$this->assertEquals("4 Giro's passage",Translate::Trans("4 Giro’s passage","UTF-8","ASCII"));

		// Czech
		$this->assertEquals("Prilis zlutoucky kun upel dabelske ody",Translate::Trans("Příliš žluťoučký kůň úpěl ďábelské ódy","UTF-8","ASCII"));
		$this->assertEquals("PRILIS ZLUTOUCKY KUN UPEL DABELSKE ODY",Translate::Trans("PŘÍLIŠ ŽLUŤOUČKÝ KŮŇ ÚPĚL ĎÁBELSKÉ ÓDY","UTF-8","ASCII"));

		// Slovak
		$this->assertEquals("klud, maso, dlzka, kopor",Translate::Trans("kľud, mäso, dĺžka, kôpor","UTF-8","ASCII"));
		$this->assertEquals("KLUD, MASO, DLZKA, KOPOR",Translate::Trans("KĽUD, MÄSO, DĹŽKA, KÔPOR","UTF-8","ASCII"));

		// Cyrillic
		$this->assertEquals("Russkyj",Translate::Trans("Русский","UTF-8","ASCII"));
		$this->assertEquals("malcyk",Translate::Trans("мальчик","UTF-8","ASCII"));
		$this->assertEquals("Oftalmologyja",Translate::Trans("Офтальмология","UTF-8","ASCII"));
		$this->assertEquals("Specyalyzacyja",Translate::Trans("Специализация","UTF-8","ASCII"));

		// German
		$this->assertEquals("Was koennen Jager absetzen?",Translate::Trans("Was können Jäger absetzen?","UTF-8","ASCII"));
		$this->assertEquals("Fuss",Translate::Trans("Fuß","UTF-8","ASCII"));

		// Symbols
		$this->assertEquals("(R) (c)",Translate::Trans("® ©","UTF-8","ASCII"));
		$this->assertEquals("? ? (Symbols)",Translate::Trans("§ • (Symbols)","UTF-8","ASCII"));

		// TODO: otestovat locale
		$_LANG_LC_ALL = "cs_CZ.UTF-8";
		$_LANG_LC_ALL = "en_US.UTF-8";
		putenv("LANG=$_LANG_LC_ALL");
		putenv("LANGUAGE=$_LANG_LC_ALL");
		setlocale(LC_MESSAGES,$_LANG_LC_ALL);
		$this->assertEquals("Jan BRUSEK",Translate::Trans("Jan BŘUŠEK","UTF-8","ASCII"));
	}

	function test_translit_whole()
	{
	$text="Příliš žluťoučký kůň úpěl ďábelské ódy! logik@centrum.cz-_' \"Řeže";
	$charset="utf8";
	$charsets=array("windows-1250", "latin2", "utf8");
	foreach($charsets as $c)
	  {
	  $t=Translate::Trans($text, $charset, $c);
	  foreach($charsets as $c2)
	    {
	    $tt=Translate::Trans($t, $c, $c2);
	    $tt=Translate::Trans($tt, $c2, $c);
	    $this->assertEquals($t, $tt);
	    }
	  }
	
	//test that this chars results in another char, even if transliterated
	//to something different
	$chars="+−–—―-„“‚‘»«…’";
	for($r=0;$r<mb_strlen($chars, "utf8");$r++)
	  {
	  $text=mb_substr($chars,$r,1, "utf8").'a';
	  foreach($charsets as $c)
      {
      $t=Translate::Trans($text, $charset, $c);
      foreach($charsets as $c2)
        {
        $tt=Translate::Trans($t, $c, $c2);
        $tt=Translate::Trans($tt, $c2, $c);
        $len=strlen($tt);
        $this->assertTrue($len > 1 && $tt[$len-1]=='a'); 
        }
      }
    }
    
  }

	function test__RemoveUtf8Chars(){
		$this->assertEquals("li?ti?ka",Translate::_RemoveUtf8Chars("lištička"));
		$this->assertEquals("li_ti_ka",Translate::_RemoveUtf8Chars("lištička",array("unknown" => "_")));
	}
  
  function test_error()
  {
    //errors handling
  $text="ŘzŘPříliš žluťoučký kůň úpěl ďábelské ódy! logik@centrum.cz-_' \"Řeže";
	$charset="utf8";
	$charsets=array("windows-1250", "latin2", "utf8");
	foreach($charsets as $c)
	  {
	  $t=Translate::Trans(substr($text,1), $charset, $c);
	  $d=Translate::Trans(substr($text,2), $charset, $c);
	  $len=strlen($d)-1;
	  $d=substr($d,1);
	  
	  foreach($charsets as $c2)
	    {
	    $tt=Translate::Trans($t, $c, $c2);
	    $tt=Translate::Trans($tt, $c2, $c);
	    $this->assertEquals($d, substr($tt,strlen($tt)-$len));
	    }
	  }
	}
	
	function translate_online($text, $from, $to)
	{
	  // Get the curl session object
  $session = curl_init('kanjidict.stc.cx/recode.php');
  // Tell curl to use HTTP POST
  curl_setopt ($session, CURLOPT_POST, true);
  // Tell curl that this is the body of the POST
  $params=array("cco" => 1, "s"=> $text, "inset" => $from, "outset" => $to);
  curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
  // Tell curl not to return headers, but do return the response
  curl_setopt($session, CURLOPT_HEADER, false);
  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($session);
  curl_close($session);
  return trim($response);
 }

 #some transliterations can be done by more ways: make them equals  
  function normalize($t)
  {
    for($r=0;$r<5;$r++) $t=strtr($t,
      array(
        ",," => "\"",
        "´" => "'",
        chr(180) => "'", #win1250 apostrof
        "`" => "'",
        "\"," => "\"'",
        "''" => "\"",
        )
      );
    return $t;
  }
 
  function test_translate_online()
  {
	if(!TRANSLATE_USE_ICONV){
		// TODO: this test fails when iconv is disabled
		return;
	}
  $text="’ŘzŘPříliš žluťoučký kůň úpěl ďábelské ódy! 
  logik@centrum.cz-_' \"Řeže
  Není tady žádný <html> tag
  ani divné znaky
  +-„“‚‘»«…’
  teď už jo";
	$c="utf8";
	$charsets=array("windows-1250", "latin2", "utf8");
	foreach($charsets as $c2)
	    {
	    $d=$this->normalize($this->translate_online($text,$c, $c2));
	    $t=$this->normalize(Translate::Trans($text, $c, $c2));
	    $this->assertEquals($t, $d);
	    $this->assertEquals(Translate::Trans($t, $c2, $c), $this->translate_online($t,$c2, $c));
	    }
  }
	
}

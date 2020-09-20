<?php
class TcLocale extends TcBase{
	function test_string_translation(){
		$this->_setLocale("cs");
		$this->assertEquals("Heslo",_("Password"));

		$this->_setLocale("en");
		$this->assertEquals("Password",_("Password"));
	}

	function test_extract_time(){
		$this->assertEquals("12:30:45",Atk14Locale::_ExtractTime(array("hours" => "12", "minutes" => "30", "seconds" => "45")));
	}

	function test_date_time_formatting(){
		$this->_setLocale("cs");
		$this->assertEquals("30.1.1977 12:33",Atk14Locale::FormatDateTime("1977-01-30 12:33:00"));
		$this->assertEquals("30.1.1977",Atk14Locale::FormatDate("1977-01-30 12:33:00"));
		$this->assertEquals("30.1.",Atk14Locale::FormatDate("1977-01-30 12:33:00","j.n."));

		$this->_setLocale("en");
		$this->assertEquals("01/30/1977 12:33",Atk14Locale::FormatDateTime("1977-01-30 12:33:00"));
		$this->assertEquals("01/30/1977",Atk14Locale::FormatDate("1977-01-30 12:33:00"));
		$this->assertEquals("30.1.",Atk14Locale::FormatDate("1977-01-30 12:33:00","j.n."));
	}

	function test_parse_date(){
		$this->_setLocale("cs");
		$this->assertEquals("1977-01-30",Atk14Locale::ParseDate("30.1.1977"));

		$this->_setLocale("en");
		$this->assertEquals("1977-01-30",Atk14Locale::ParseDate("01/30/1977"));

		// errors
		$this->assertEquals(null,Atk14Locale::ParseDate("nonsence"));
	}

	function test_parse_datetime(){
		$this->_setLocale("cs");
		$this->assertEquals("1977-01-30 12:33:00",Atk14Locale::ParseDateTime("30.1.1977 12:33"));
		$this->assertEquals("1977-01-30 00:00:00",Atk14Locale::ParseDateTime("30.1.1977"));

		// errors
		$this->assertEquals(null,Atk14Locale::ParseDateTime("nonsence"));
		$this->assertEquals(null,Atk14Locale::ParseDateTime("30.1.1977 12:33:22"));

		$this->_setLocale("en");
		$this->assertEquals("1977-01-30 12:33:00",Atk14Locale::ParseDateTime("01/30/1977 12:33"));
		$this->assertEquals("1977-01-30 00:00:00",Atk14Locale::ParseDateTime("01/30/1977"));
	}

	function test_parse_datetime_with_seconds(){
		$this->_setLocale("cs");
		$this->assertEquals("1977-01-30 12:33:22",Atk14Locale::ParseDateTimeWithSeconds("30.1.1977 12:33:22"));
		$this->assertEquals("1977-01-30 12:33:00",Atk14Locale::ParseDateTimeWithSeconds("30.1.1977 12:33"));
		$this->assertEquals("1977-01-30 00:00:00",Atk14Locale::ParseDateTimeWithSeconds("30.1.1977"));

		$this->_setLocale("en");
		$this->assertEquals("1977-01-30 12:33:22",Atk14Locale::ParseDateTimeWithSeconds("01/30/1977 12:33:22"));
		$this->assertEquals("1977-01-30 12:33:00",Atk14Locale::ParseDateTimeWithSeconds("01/30/1977 12:33"));
		$this->assertEquals("1977-01-30 00:00:00",Atk14Locale::ParseDateTimeWithSeconds("01/30/1977"));

		// errors
		$this->assertEquals(null,Atk14Locale::ParseDateTimeWithSeconds("nonsence"));
	}

	function test_InitializeLocale(){
		global $ATK14_GLOBAL;

		$this->_setLocale("cs");

		$this->assertEquals("cs",$ATK14_GLOBAL->getLang());

		$lang = "sk";
		$previous = Atk14Locale::Initialize($lang);
		$this->assertEquals("sk",$lang);
		$this->assertEquals("cs",$previous);
		$this->assertEquals("sk",$ATK14_GLOBAL->getLang());

		$lang = "en";
		$previous = Atk14Locale::Initialize($lang);
		$this->assertEquals("en",$lang);
		$this->assertEquals("sk",$previous);
		$this->assertEquals("en",$ATK14_GLOBAL->getLang());

		$lang = "xy"; // nonsence -> it must be changed automatically to the default language
		$previous = Atk14Locale::Initialize($lang);
		$this->assertEquals("cs",$lang);
		$this->assertEquals("en",$previous);
		$this->assertEquals("cs",$ATK14_GLOBAL->getLang());

		// Initializing environment to the default language (cs)

		// ... first set a language other than the default
		$lang = "en";
		Atk14Locale::Initialize($lang);
		$this->assertEquals("en",$ATK14_GLOBAL->getLang());

		// ... and now set the default language
		Atk14Locale::Initialize();
		$this->assertEquals("cs",$ATK14_GLOBAL->getLang());
	}

	function test_number_format(){
		$this->_setLocale("cs");

		$this->assertEquals(",",Atk14Locale::DecimalPoint());
		$this->assertEquals(" ",Atk14Locale::ThousandsSeparator());

		$this->assertEquals("123",Atk14Locale::FormatNumber(123));
		$this->assertEquals("123",Atk14Locale::FormatNumber(123.0));
		$this->assertEquals("123,0",Atk14Locale::FormatNumber("123.0"));
		$this->assertEquals("1 234",Atk14Locale::FormatNumber(1234));
		$this->assertEquals("-1 222,3333",Atk14Locale::FormatNumber(-1222.3333));
		$this->assertEquals("-1 222,7777",Atk14Locale::FormatNumber(-1222.7777000));
		$this->assertEquals("-1 222,0000",Atk14Locale::FormatNumber("-1222.0000"));

		$this->assertEquals("-1 222,78",Atk14Locale::FormatNumber(-1222.7777000,2));
		$this->assertEquals("-1 223",Atk14Locale::FormatNumber(-1222.7777000,0));
		$this->assertEquals("-1 222,00",Atk14Locale::FormatNumber("-1222.0000",2));

		$this->_setLocale("en");

		$this->assertEquals(".",Atk14Locale::DecimalPoint());
		$this->assertEquals(",",Atk14Locale::ThousandsSeparator());

		$this->assertEquals("123",Atk14Locale::FormatNumber(123));
		$this->assertEquals("123",Atk14Locale::FormatNumber(123.0));
		$this->assertEquals("123.0",Atk14Locale::FormatNumber("123.0"));
		$this->assertEquals("1,234",Atk14Locale::FormatNumber(1234));
		$this->assertEquals("-1,222.3333",Atk14Locale::FormatNumber(-1222.3333));
		$this->assertEquals("-1,222.7777",Atk14Locale::FormatNumber(-1222.7777000));
		$this->assertEquals("-1,222.0000",Atk14Locale::FormatNumber("-1222.0000"));

		$this->assertEquals("-1,222.78",Atk14Locale::FormatNumber(-1222.7777000,2));
		$this->assertEquals("-1,223",Atk14Locale::FormatNumber(-1222.7777000,0));
		$this->assertEquals("-1,222.00",Atk14Locale::FormatNumber("-1222.0000",2));
	}

	function _setLocale($lang){
		$set_lang = $lang;
		Atk14Locale::Initialize($set_lang);
		$this->assertEquals($lang,$set_lang);
	}

}

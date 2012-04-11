<?php
class TcLocale extends TcBase{
	function test_extract_time(){
		$this->assertEquals("12:30:45",Atk14Locale::_ExtractTime(array("hours" => "12", "minutes" => "30", "seconds" => "45")));
	}

	function test_format_datetime(){
		$this->assertEquals("30.1.1977 12:33",Atk14Locale::FormatDateTime("1977-01-30 12:33:00"));
	}

	function test_parse_date(){
		$this->assertEquals("1977-01-30",Atk14Locale::ParseDate("30.1.1977"));

		// errors
		$this->assertEquals(null,Atk14Locale::ParseDate("nonsence"));
	}

	function test_parse_datetime(){
		$this->assertEquals("1977-01-30 12:33:00",Atk14Locale::ParseDateTime("30.1.1977 12:33"));
		$this->assertEquals("1977-01-30 00:00:00",Atk14Locale::ParseDateTime("30.1.1977"));

		// errors
		$this->assertEquals(null,Atk14Locale::ParseDateTime("nonsence"));
		$this->assertEquals(null,Atk14Locale::ParseDateTime("30.1.1977 12:33:22"));
	}

	function test_parse_datetime_with_seconds(){
		$this->assertEquals("1977-01-30 12:33:22",Atk14Locale::ParseDateTimeWithSeconds("30.1.1977 12:33:22"));
		$this->assertEquals("1977-01-30 12:33:00",Atk14Locale::ParseDateTimeWithSeconds("30.1.1977 12:33"));
		$this->assertEquals("1977-01-30 00:00:00",Atk14Locale::ParseDateTimeWithSeconds("30.1.1977"));

		// errors
		$this->assertEquals(null,Atk14Locale::ParseDateTimeWithSeconds("nonsence"));
	}

}

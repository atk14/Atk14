<?
class tc_atk14_locale extends tc_base{
	function test_extract_time(){
		$this->assertEquals("12:30:45",Atk14Locale::_ExtractTime(array("hours" => "12", "minutes" => "30", "seconds" => "45")));
	}

	function test_format_datetime(){
		$this->assertEquals("30.1.1977 12:33",Atk14Locale::FormatDateTime("1977-01-30 12:33:00"));
	}

	function test_parse_datetime(){
		$this->assertEquals("1977-01-30 12:33:00",Atk14Locale::ParseDateTime("30.1.1977 12:33"));
	}
}
?>

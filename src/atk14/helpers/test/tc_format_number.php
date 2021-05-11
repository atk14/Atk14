<?php
class TcFormatNumber extends TcBase {

	function test(){
		$this->assertEquals("1 012,345",smarty_modifier_format_number(1012.345));
		$this->assertEquals("1 012,35",smarty_modifier_format_number(1012.345,2));
		$this->assertEquals("1 012,34500",smarty_modifier_format_number(1012.345,5));
		$this->assertEquals("1 012",smarty_modifier_format_number(1012.345,0));

		// zeroes
		$this->assertEquals("0",smarty_modifier_format_number("0"));
		$this->assertEquals("0",smarty_modifier_format_number("+0"));
		$this->assertEquals("0",smarty_modifier_format_number("-0"));

		// decimal places auto-detection
		$this->assertEquals("-11",smarty_modifier_format_number("-11"));
		$this->assertEquals("-11,00",smarty_modifier_format_number("-11.00"));
		$this->assertEquals("0,00",smarty_modifier_format_number("-0.00"));

		// nulls
		$this->assertEquals("",smarty_modifier_format_number(""));
		$this->assertEquals("",smarty_modifier_format_number(null));
	}
}

<?php
class TcEmailField extends TcBase {

	function test(){
		$field = new EmailField(array());

		list($err,$value) = $field->clean(" john@doe.com ");
		$this->assertNull($err);
		$this->assertEquals("john@doe.com",$value);

		list($err,$value) = $field->clean(" john@ ");
		$this->assertEquals("Enter a valid e-mail address.",$err);
		$this->assertNull($value);

		list($err,$value) = $field->clean(" ");
		$this->assertEquals("This field is required.",$err);
		$this->assertNull($value);

		list($err,$value) = $field->clean(" @ ");
		$this->assertEquals("This field is required.",$err);
		$this->assertNull($value);
	}
}

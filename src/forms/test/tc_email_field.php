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

		list($err,$value) = $field->clean("~ john@doe.com ");
		$this->assertEquals("Enter a valid e-mail address.",$err);
		$this->assertNull($value);

		//

		$field = new EmailField(array("required" => false));

		list($err,$value) = $field->clean(" ");
		$this->assertNull($err);
		$this->assertNull($value);

		list($err,$value) = $field->clean(" @ ");
		$this->assertNull($err);
		$this->assertNull($value);

		list($err,$value) = $field->clean("john@doe.com");
		$this->assertNull($err);
		$this->assertEquals("john@doe.com",$value);
	}

	function test_format_initial_data(){
		$f = new Form();
		$f->add_field("email", new EmailField(array(
			"initial" => NULL,
		)));
		$f->add_field("email2", new EmailField(array(
			"initial" => "",
		)));
		$f->add_field("email3", new EmailField(array(
			"initial" => "john@doe.com",
		)));

		$field = $f->get_field("email");
		$this->assertStringContains('value="@"',$field->as_widget());

		$field2 = $f->get_field("email2");
		$this->assertStringContains('value="@"',$field2->as_widget());

		$field3 = $f->get_field("email3");
		$this->assertStringContains('value="john@doe.com"',$field3->as_widget());
	}
}

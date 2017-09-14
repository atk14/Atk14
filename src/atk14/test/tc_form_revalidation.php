<?php
require_once(dirname(__FILE__)."/app/forms/test_form.php");

class TcFormRevalidation extends TcBase {

	function test(){

		// The same form can be validated again and again...

		$form = new TestForm();
		
		$form->set_initial(array(
			"firstname" => "John",
			"lastname" => "Doe", // !! disabled field
			"nickname" => "doe", // !! disabled field
		));

		$data = $form->validate(array("firstname" => " ")); // firstname is mandatory
		$this->assertEquals(false,$form->is_valid());
		$this->assertEquals(true,$form->has_errors());
		$this->assertEquals(null,$data);

		$data = $form->validate(array(
			"firstname" => "Jack",
			"lastname" => "Foobar",
			"login" => "bad_guy",
		));
		$this->assertEquals(true,$form->is_valid());
		$this->assertEquals(false,$form->has_errors());
		$this->assertEquals(array(
			"firstname" => "Jack",
			"lastname" => "Doe",
			"nickname" => "doe",
		),$data);

		$data = $form->validate(null); // null means an empty array in this case
		$this->assertEquals(false,$form->is_valid());
		$this->assertEquals(true,$form->has_errors());
		$this->assertEquals(null,$data);

		$data = $form->validate(array(
			"firstname" => "Samantha",
			"lastname" => "Foobar",
			"login" => "bad_girl",
		));
		$this->assertEquals(true,$form->is_valid());
		$this->assertEquals(false,$form->has_errors());
		$this->assertEquals(array(
			"firstname" => "Samantha",
			"lastname" => "Doe",
			"nickname" => "doe",
		),$data);
	}
}

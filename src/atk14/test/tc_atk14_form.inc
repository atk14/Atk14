<?php
class tc_atk14_form extends tc_base{
	function test_validation_with_disabled_fields(){
		$form = new TestForm();
		$form->set_initial(array(
			"nickname" => "jumper",
		));

		$d = $form->validate(array(
			"firstname" => "John",
			"lastname" => "Doe",
			"nickname" => "mx23",
		));

		$this->assertEquals("John",$d["firstname"]);
		$this->assertEquals("Smith",$d["lastname"]);
		$this->assertEquals("jumper",$d["nickname"]);
	}

	function test_csfr_tokens(){
		$form = new Atk14Form();
		$current_token = $form->get_csfr_token();

		$tokens = $form->get_valid_csfr_tokens();

		$this->assertEquals($current_token,$tokens[0]);
		$this->assertTrue(sizeof($tokens)>1);
		$this->assertTrue($tokens[0]!=$tokens[1]);
	}
}

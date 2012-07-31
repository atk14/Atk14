<?php
class TcForm extends TcBase{
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

	function test_get_initial(){
		$form = new Atk14Form();
		$this->assertEquals(array(),$form->get_initial());

		$form->add_field("firstname",new CharField());
		$form->add_field("lastname",new CharField(array("initial" => "Smith")));

		$this->assertEquals(array("firstname" => null, "lastname" => "Smith"),$form->get_initial());

		$form->set_initial("firstname","John");

		$this->assertEquals(array("firstname" => "John", "lastname" => "Smith"),$form->get_initial());
	}

	function test_csrf_tokens(){
		$form = new Atk14Form();
		$current_token = $form->get_csrf_token();

		$tokens = $form->get_valid_csrf_tokens();

		$this->assertEquals($current_token,$tokens[0]);
		$this->assertTrue(sizeof($tokens)>1);
		$this->assertTrue($tokens[0]!=$tokens[1]);
	}

	function test_camelcase_aliasses(){
		$form = new TestForm();
		$form->setInitial(array("nickname" => "Hammer"));
		$this->assertEquals(array(
			"firstname" => null,
			"lastname" => "Smith",
			"nickname" => "Hammer",
		),$form->getInitial());
	}

	function test_get_default_form(){
		$form = Atk14Form::GetDefaultForm();
		$this->assertEquals("ApplicationForm",get_class($form));

		// default form in *admin* namespace should be AdminForm (see app/forms/admin/admin_form.php)
		$client = new Atk14Client();
		$controller = $client->get("admin/en/main/index");
		$this->assertEquals("AdminForm",get_class($controller->form));
	}
}

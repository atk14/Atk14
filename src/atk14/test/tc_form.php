<?php
require_once(dirname(__FILE__)."/app/forms/test_form.php");
require_once(dirname(__FILE__)."/app/forms/before_and_after_set_up_form.php");
class TcForm extends TcBase{
	function test_constructor(){
		$form = new TestForm();
		$this->assertEquals(null,$form->prefix);
		$form->validate(array());
		$this->assertEquals(null,$form->prefix);

		$form = new TestForm(array(
			"prefix" => "test",
		));
		$this->assertEquals("test",$form->prefix);
		$form->prefix = "xxx";
		$form->validate(array()); // __do_small_initialization() must not be called
		$this->assertEquals("xxx",$form->prefix);
	}

	function test_before_and_after_set_up(){
		$form = new BeforeAndAfterSetUpForm();
		$form2 = new BeforeAndAfterSetUpForm();

		$form->add_field("extern_field", new CharField(array("initial" => "ok")));
		$form2->add_field("extern_field", new CharField(array("initial" => "ok")));

		$this->assertEquals($exp = array(
			"before_set_up" => "ok",
			"set_up" => "ok",
			"extern_field" => "ok"
		),$form->get_initial());
		$this->assertEquals($exp,$form2->get_initial());

		// after_set_up() must be called just before data validation or displaying of the given form
		$cleaned_data = $form->validate(array("before_set_up" => "set", "set_up" => "set", "after_set_up" => "set", "extern_field" => "set"));
		$form2->begin();

		$this->assertEquals($exp = array(
			"before_set_up" => "ok",
			"set_up" => "ok",
			"extern_field" => "ok",
			"after_set_up" => "ok",
		),$form->get_initial());
		$this->assertEquals($exp,$form2->get_initial());

		$this->assertEquals(array(
			"before_set_up" => "set",
			"set_up" => "set",
			"extern_field" => "set",
			"after_set_up" => "set",
		),$cleaned_data);

		// --

		$form = new BeforeAndAfterSetUpForm();

		$this->assertEquals(true,$form->has_field("before_set_up"));
		$this->assertEquals(true,$form->has_field("set_up"));
		$this->assertEquals(false,$form->has_field("after_set_up"));

		$form->begin();

		$this->assertEquals(true,$form->has_field("after_set_up"));

		// --

		$form = new BeforeAndAfterSetUpForm();
		$this->assertEquals(array("before_set_up","set_up","after_set_up"),$form->get_field_keys(),"after_set_up() must be called before get_field_keys()");
	}

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

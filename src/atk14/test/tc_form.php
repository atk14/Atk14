<?php
require_once(dirname(__FILE__)."/app/forms/test_form.php");
require_once(dirname(__FILE__)."/app/forms/pre_and_post_set_up_form.php");
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

	function test_before_and_post_set_up(){
		$form = new PreAndPostSetUpForm();
		$form2 = new PreAndPostSetUpForm();

		$form->add_field("extern_field", new CharField(array("initial" => "ok")));
		$form2->add_field("extern_field", new CharField(array("initial" => "ok")));

		$this->assertEquals($exp = array(
			"pre_set_up" => "ok",
			"set_up" => "ok",
			"extern_field" => "ok"
		),$form->get_initial());
		$this->assertEquals($exp,$form2->get_initial());

		// post_set_up() must be called just before data validation or displaying of the given form
		$cleaned_data = $form->validate(array("pre_set_up" => "set", "set_up" => "set", "post_set_up" => "set", "extern_field" => "set"));
		$form2->begin();

		$this->assertEquals($exp = array(
			"pre_set_up" => "ok",
			"set_up" => "ok",
			"extern_field" => "ok",
			"post_set_up" => "ok",
		),$form->get_initial());
		$this->assertEquals($exp,$form2->get_initial());

		$this->assertEquals(array(
			"pre_set_up" => "set",
			"set_up" => "set",
			"extern_field" => "set",
			"post_set_up" => "set",
		),$cleaned_data);

		// --

		$form = new PreAndPostSetUpForm();

		$this->assertEquals(true,$form->has_field("pre_set_up"));
		$this->assertEquals(true,$form->has_field("set_up"));
		$this->assertEquals(false,$form->has_field("post_set_up"));

		$form->begin();

		$this->assertEquals(true,$form->has_field("post_set_up"));

		// --

		$form = new PreAndPostSetUpForm();
		$this->assertEquals(array("pre_set_up","set_up","post_set_up"),$form->get_field_keys(),"post_set_up() must be called before get_field_keys()");
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

	function test_multipart(){
		$form = new TestForm();
		$form->add_field("name",new CharField());
		$this->assertNotContains('enctype="multipart/form-data"',$form->begin());

		$form = new TestForm();
		$form->add_field("name",new CharField());
		$form->add_field("image",new ImageField());
		$this->assertContains('enctype="multipart/form-data"',$form->begin());

		$form = new TestForm();
		$form->add_field("name",new CharField());
		$form->enable_multipart();
		$this->assertContains('enctype="multipart/form-data"',$form->begin());
	}

	function test_set_action(){
		global $HTTP_REQUEST,$ATK14_GLOBAL;
		$HTTP_REQUEST->setRequestUri("/testing/?id=12&format=xml");

		$form = new TestForm();
		$this->assertEquals('/testing/?id=12&format=xml',$form->get_action());

		$form->set_action('/new-uri/');
		$this->assertEquals('/new-uri/',$form->get_action());

		$form->set_action(array(
			"lang" => "en",
			"namespace" => "",
			"controller" => "books",
			"action" => "detail",
			"id" => 123,
			"format" => "raw",
		));
		$this->assertEquals('/en/books/detail/?id=123&format=raw',$form->get_action());

		$ATK14_GLOBAL->setValue("namespace","");
		$ATK14_GLOBAL->setValue("lang","en");
		$ATK14_GLOBAL->setValue("controller","articles");
		$ATK14_GLOBAL->setValue("action","index");
		
		$form->set_action("export");
		$this->assertEquals("/en/articles/export/",$form->get_action());

		$form->set_action("books/index");
		$this->assertEquals("/en/books/",$form->get_action());
	}
}

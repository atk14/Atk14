<?php
define("ATK14_DOCUMENT_ROOT",__DIR__."/");
require(__DIR__."/../../../../load.php");

class TestForm extends Atk14Form {

	function set_up(){
		$this->add_field("name", new CharField(array(
			"label" => "Enter your name",
			"required" => true,
			"max_length" => 50,
			"initial" => "John Doe",
		)));
	}
}

class TcField extends TcBase {

	function test(){
		Atk14Require::Helper("modifier.field");
		$form = new TestForm();

		$this->assertEquals('<input maxlength="50" required="required" type="text" name="name" class="text form-control" id="id_name" value="John Doe" />',smarty_modifier_field($form,"name"));
		$this->assertEquals('<input maxlength="50" required="required" placeholder="Enter your name" type="text" name="name" class="text form-control" id="id_name" value="John Doe" />',smarty_modifier_field($form,"name","label_to_placeholder"));
		$this->assertEquals('<input maxlength="50" required="required" type="text" name="name" class="text form-control" id="id_name" value="John Doe" />',smarty_modifier_field($form,"name"));
	}
}

<?php
class TestForm extends Atk14Form{
	function set_up(){
		$this->add_field("firstname",new CharField());
		$this->add_field("lastname",new CharField([
			"disabled" => true,
			"initial" => "Smith",
		]));
		$this->add_field("nickname",new CharField([
			"disabled" => true,
			"initial" => "smither",
		]));
	}
}

<?php
class BeforeAndAfterSetUpForm extends Atk14Form{
	function before_set_up(){
		$this->add_field("before_set_up", new CharField(array("initial" => "ok")));
	}

	function set_up(){
		$this->add_field("set_up", new CharField(array("initial" => "ok")));
	}

	function after_set_up(){
		$this->add_field("after_set_up", new CharField(array("initial" => "ok")));
	}
}

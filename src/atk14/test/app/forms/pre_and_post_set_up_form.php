<?php
class PreAndPostSetUpForm extends Atk14Form{
	function pre_set_up(){
		$this->add_field("pre_set_up", new CharField(array("initial" => "ok")));
	}

	function set_up(){
		$this->add_field("set_up", new CharField(array("initial" => "ok")));
	}

	function post_set_up(){
		$this->add_field("post_set_up", new CharField(array("initial" => "ok")));
	}
}

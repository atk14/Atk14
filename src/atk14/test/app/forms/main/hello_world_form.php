<?php
class HelloWorldForm extends ApplicationForm{
	function set_up(){
		$this->add_field("greeting",new CharField(array(
		)));
	}
}

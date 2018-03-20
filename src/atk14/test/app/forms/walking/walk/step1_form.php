<?php
class Step1Form extends ApplicationForm {

	function set_up(){
		$this->add_field("name",new CharField(array(
		)));
	}
}

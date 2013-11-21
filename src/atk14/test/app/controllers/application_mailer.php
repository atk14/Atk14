<?php
class ApplicationMailer extends Atk14Mailer {
	var $from = "Unit Testing <unit@testing.com>";

	function ordinary_notification($way = ""){
		$this->subject = "Ordinary notification";
		$this->to = "john.doe@hotmail.com";

		$this->tpl_data["way"] = $way;
	}

	function user_data_summary_notification($login,$email,$password){
		$this->to = $email;
		$this->tpl_data += array(
			"login" => $login,
			"email" => $email,
			"password" => $password,
		);
	}

	function html_notification($params = array()){
		$this->subject = "Rich formatted notification";
		$this->to = "john.doe@hotmail.com";
	}

	function testing_hooks($params = array()){
		
	}

	function _before_filter(){
		$this->tpl_data["value_added_in_before_filter"] = "OK (bf)";
	}

	function _before_render(){
		$this->tpl_data["value_added_in_before_render"] = "OK (br)";
	}

	function _after_render(){
		$this->body = str_replace("%after_render_placeholder%","OK (ar)",$this->body);
	}
}

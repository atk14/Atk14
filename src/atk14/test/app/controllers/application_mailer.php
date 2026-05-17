<?php
class ApplicationMailer extends Atk14Mailer {

	public $from = "Unit Testing <unit@testing.com>";

	function ordinary_notification($way = ""){
		$this->subject = "Ordinary notification";
		$this->to = "john.doe@hotmail.com";

		$this->tpl_data["way"] = $way;
	}

	function user_data_summary_notification($login,$email,$password){
		$this->to = $email;
		$this->tpl_data += [
			"login" => $login,
			"email" => $email,
			"password" => $password,
		];
	}

	function html_notification($params = []){
		$this->subject = "Rich formatted notification";
		$this->to = "john.doe@hotmail.com";
	}

	function html_only_notification($params = []){
		$this->subject = "HTML only notification";
		$this->to = "john.doe@hotmail.com";
	}

	function html_notification_without_layout($params = []){
		$this->subject = "Rich formatted notification";
		$this->to = "john.doe@hotmail.com";
		$this->template_name = "html_notification";
		$this->render_layout = false;
	}

	function html_notification_christmas_theme($params = []){
		$this->subject = "Rich formatted notification";
		$this->to = "john.doe@hotmail.com";
		$this->template_name = "html_notification";
		$this->layout_name = "mailer_christmas_theme";
	}

	function notification_without_templates($params = []){
		// There is no template for this action.
		// An exception should be thrown.
	}

	function testing_hooks($params = []){
		
	}

	function test_rendering(){
		
	}

	function simple_message_without_layout(){
		$this->render_layout = false;
		$this->cc = "big@brother.com";
	}

	function send_attachment(){
		$this->render_layout = false;
		$this->subject = "subject";
		$this->body = "body";
		$this->add_attachment("Hello world!","greeting.txt","text/plain");
	}

	function _before_filter(){
		$this->tpl_data["value_added_in_before_filter"] = "OK (bf)";
	}

	function _before_render(){
		$this->tpl_data["value_added_in_before_render"] = "OK (br)";

		$this->tpl_data["footer_company"] = "SnakeOil ltd";
		$this->tpl_data["footer_email"] = "info@snakeoil.com";
		$this->tpl_data["footer_url"] = "http://snakeoil.com/";
	}

	function _after_render(){
		$this->body = str_replace("%after_render_placeholder%","OK (ar)",$this->body);
	}
}

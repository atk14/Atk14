<?php
class ApplicationMailer extends Atk14Mailer {
	var $from = "Unit Testing <unit@testing.com>";
	function ordinary_notification($params = array()){
		$this->subject = "Ordinary notification";
		$this->to = "john.doe@hotmail.com";
	}

	function html_notification($params = array()){
		$this->subject = "Rich formatted notification";
		$this->to = "john.doe@hotmail.com";
	}
}

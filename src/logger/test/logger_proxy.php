<?php
class LoggerProxy extends Logger {

	public function send_email_notification(){
		return $this->_send_email_notification();
	}
}

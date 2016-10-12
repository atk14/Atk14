<?php
class Atk14FlashMessage {
	protected $message = "";
	protected $type;

	/**
	 * $message = new Atk14FlashMessage("You have been logged out successfully","notice");
	 */
	function __construct($message,$type) {
		$this->message = $message;
		$this->type = $type;
	}

	function getMessage(){
		return $this->message;
	}

	/**
	 * echo $message->getType(); // "notice"
	 */
	function getType(){
		return $this->type;
	}

	function toString(){ return $this->message; }
	function __toString(){ return $this->toString(); }
}

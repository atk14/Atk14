<?php
/**
 * Class for holding messages of various types
 *
 * @filesource
 */

/**
 * Class for holding messages of various types
 *
 * @package Atk14\Core
 */
class Atk14FlashMessage {
	/**
	 * Content of the flash message
	 *
	 * @var string
	 */
	protected $message = "";

	/**
	 * Type of the message
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * ```
	 * $message = new Atk14FlashMessage("You have been logged out successfully","notice");
	 * ```
	 */
	function __construct($message,$type) {
		$this->message = $message;
		$this->type = $type;
	}

	/**
	 * Returns content of the message.
	 *
	 * @return string
	 */
	function getMessage(){
		return $this->message;
	}

	/**
	 * Returns type of the message
	 *
	 * ```
	 * echo $message->getType(); // "notice"
	 * ```
	 *
	 * @return string
	 */
	function getType(){
		return $this->type;
	}

	function toString(){ return $this->message; }
	function __toString(){ return $this->toString(); }
}

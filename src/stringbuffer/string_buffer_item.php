<?php
/**
 * Element to be added to StringBuffer as string
 *
 * @package Atk14\StringBuffer
 */
class StringBufferItem{

	protected $_String = null;

	/**
	 * Initializes file buffer element
	 *
	 * @param string $string
	 */
	function __construct($string){
		$this->_String = (string)$string;
	}

	function getContent(){
		return $this->_String;
	}

	/**
	 * Returns length of string in buffer.
	 *
	 * @return int
	 */
	function getLength(){ return strlen($this->getContent()); }

	function flush(){ echo $this->getContent(); }

	/**
	 * Returns string representation of the object.
	 *
	 * @return string
	 */
	final function toString(){ return $this->getContent(); }

	/**
	 * Method that returns string representation of the object.
	 */
	final function __toString(){ return $this->toString(); }

	/**
	 * Replace part of string in buffer
	 *
	 * @param string $search
	 * @param string $replace
	 */
	function replace($search,$replace){
		$this->_String = str_replace($search,$replace,$this->_String);
	}

	function substr($offset,$length = null){
		if(is_null($length)){
			return substr($this->_String,$offset);
		}
		return substr($this->_String,$offset,$length);
	}
}

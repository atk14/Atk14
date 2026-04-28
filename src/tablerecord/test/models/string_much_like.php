<?php
class StringMuchLike {
	
	protected $_str;

	function __construct($string){ $this->_str = $string; }
	function toString(){ return $this->_str; }
	function __toString(){ return "$this->_str (__toString)"; }
}

<?php
class StringLike {
	function __construct($string){ $this->_str = $string; }
	function __toString(){ return $this->_str; }
}

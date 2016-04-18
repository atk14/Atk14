<?php
class IntLike {
	function __construct($int){ $this->_val = (int)$int; }
	function getId(){ return $this->_val; }
}

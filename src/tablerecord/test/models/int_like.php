<?php
class IntLike {

	protected $_val;

	function __construct($int){ $this->_val = (int)$int; }
	function getId(){ return $this->_val; }
}

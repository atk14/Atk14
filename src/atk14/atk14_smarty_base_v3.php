<?php
/**
 * A middle layer for Smarty version 3.
 */
class Atk14SmartyBase extends SmartyBC{

	function __construct(){
		parent::__construct();

		$this->setErrorReporting(E_ALL ^ E_NOTICE);
	}

}

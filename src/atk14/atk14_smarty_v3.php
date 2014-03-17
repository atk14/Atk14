<?php
/**
 * A middle layer for Smarty version 3.
 */
class Atk14Smarty extends SmartyBC{
	protected $atk14_contents = array();

	function __construct(){
		parent::__construct();

		$this->setErrorReporting(E_ALL ^ E_NOTICE);
	}

	/**
	 * $smarty->addAtk14Content("main","<p>Well...</p>");
	 * $smarty->addAtk14Content("main","<p>Well...</p>",array(
	 *	"strategy" => "replace"
	 * ));
	 */
	function addAtk14Content($key,$content = "",$options = array()){
		return _smarty_addAtk14Content($this,$this->atk14_contents,$key,$content,$options);
	}

	function getAtk14Content($key){
		return $this->atk14_contents[$key];
	}

	function getAtk14ContentKeys(){ return array_keys($this->atk14_contents); }

}

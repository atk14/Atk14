<?php
class Atk14Smarty extends Atk14SmartyBase{
	protected $atk14_contents = array();

	/**
	 * Permissions for smarty directory structure
	 */
	var $_dir_perms  = ATK14_SMARTY_DIR_PERMS;

	/**
	 * Permissions used for files created by smarty
	 */
	var $_file_perms = ATK14_SMARTY_FILE_PERMS;

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

	function clearAtk14Contents(){
		$this->atk14_contents = array();
	}

	function getAtk14ContentKeys(){ return array_keys($this->atk14_contents); }


}

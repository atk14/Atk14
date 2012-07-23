<?
/**
 * A middle layer for Smarty version 3.
 */
class Atk14Smarty extends SmartyBC{
	protected $atk14_contents = array();

	function addAtk14Content($key,$content = ""){
		if(!isset($this->atk14_contents[$key])){ $this->atk14_contents[$key] = $content; return; }
		$this->atk14_contents[$key] .= $content;
	}

	function getAtk14Content($key){
		return $this->atk14_contents[$key];
	}

	function getAtk14ContentKeys(){ return array_keys($this->atk14_contents); }

}

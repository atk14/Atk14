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
		$out = '<%atk14_initial_content%>';
		$default_strategy = "append";
		foreach(array_reverse($this->atk14_contents[$key]) as $item){
			list($content,$options) = $item;
			if(isset($options["default_strategy"])){
				$default_strategy = $options["default_strategy"];
				break;
			}
		}

		foreach($this->atk14_contents[$key] as $item){
			if(sizeof($item)!=2){ // see function _smarty_addAtk14Content()
				throw new Exception("Atk14Smarty: \$item doesn't contain two elements");
			}
			list($content,$options) = $item;

			$strategy = $options["strategy"] ? $options["strategy"] : $default_strategy;

			switch($strategy){
				case 'prepend':
					$out = $content.$out;
					break;
				case 'replace':
					$out = $content;
					break;
				case '_place_initial_content_':
					$out = str_replace('<%atk14_initial_content%>',$content,$out);
					break;
				default: // "append"	
					$out .= $content;
			}
		}
		return $out;
	}

	function clearAtk14Contents(){
		$this->atk14_contents = array();
	}

	function getAtk14ContentKeys(){ return array_keys($this->atk14_contents); }
}

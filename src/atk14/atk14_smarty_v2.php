<?php
/**
 * A middle layer for Smarty version 2.
 */
class Atk14Smarty extends Smarty{
	protected $atk14_contents = array();

	/**
	 * See Atk14Smarty::addAtk14Content() in atk14_smarty_v3.php
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

	// methods for forward compatibility

	function setTemplateDir($dir){
		$this->template_dir = $dir;
	}

	function setCompileDir($dir){
		$this->compile_dir = $dir;
	}

	function setConfigDir($dir){
		$this->config_dir = $dir;
	}

	function setCacheDir($dir){
		$this->cache_dir = $dir;
	}

	/**
	 *	$smarty->setPluginsDir(array(
	 *		"most_preferred_directory",
	 *		"alternative_directory"
	 *	));
	 */
	function setPluginsDir($dir){
		if(!is_array($dir)){ $dir = array($dir); }
		$this->plugins_dir = $dir;
	}

	function getPluginsDir(){
		return $this->plugins_dir;
	}

	function registerFilter($type,$callback){
		if($type=="pre"){
			$this->register_prefilter($callback);
		}
	}

	function assignByRef($name,&$value){
		return $this->assign_by_ref($name,$value);
	}

	function getTemplateVars($key = null){
		if(isset($key)){
			return $this->_tpl_vars[$key];
		}
		return $this->_tpl_vars;
	}

	function clearAllAssign(){
		return $this->clear_all_assign();
	}

	function templateExists($template){
		return $this->template_exists($template);
	}
}

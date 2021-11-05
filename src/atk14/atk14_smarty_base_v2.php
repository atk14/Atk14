<?php
/**
 * A middle layer for Smarty version 2.
 */
class Atk14SmartyBase extends Smarty{

	static $ATK14_RENDERED_TEMPLATES = array();

	static protected $LAST_TPL_REF;

	function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null){
		if(PRODUCTION){
			return parent::fetch($template, $cache_id, $compile_id, $parent);
		}

		// Experimental feature for capturing all rendered templates
		// 
		if(!isset(self::$LAST_TPL_REF)){
			self::$LAST_TPL_REF = &self::$ATK14_RENDERED_TEMPLATES;
		}

		$prev_ref = &self::$LAST_TPL_REF;

		$template_fullpath = $template;
		foreach($this->getTemplateDir() as $d){
			if(file_exists($d."/".$template_fullpath)){
				$template_fullpath = $d."/".$template_fullpath;
				break;
			}
		}

		$children = array();
		self::$LAST_TPL_REF[] = array(
			"template" => $template_fullpath,
			"children" => &$children,
		);

		self::$LAST_TPL_REF = &$children;
		$ret = parent::fetch($template, $cache_id, $compile_id, $parent);
		self::$LAST_TPL_REF = &$prev_ref;

		return $ret;
	}

	// methods for forward compatibility

	function setTemplateDir($dir){
		$this->template_dir = $dir;
	}

	function getTemplateDir(){
		return $this->template_dir;
	}

	function setCompileDir($dir){
		$this->compile_dir = $dir;
	}

	function setForceCompile($set = true){
		$this->force_compile = $set;
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
			return isset($this->_tpl_vars[$key]) ? $this->_tpl_vars[$key] : null;
		}
		return $this->_tpl_vars;
	}

	function clearAllAssign(){
		return $this->clear_all_assign();
	}

	function templateExists($template){
		return $this->template_exists($template);
	}

	function setErrorReporting($error_reporting){
		// There is no method setErrorReporting in Smarty2.
		// Just don't do anything.
	}
}

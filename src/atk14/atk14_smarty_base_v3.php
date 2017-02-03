<?php
/**
 * A middle layer for Smarty version 3.
 */
class Atk14SmartyBase extends SmartyBC{

	static $ATK14_RENDERED_TEMPLATES = array();

	static protected $LAST_TPL_REF;

	function __construct(){
		parent::__construct();

		$this->setErrorReporting(E_ALL ^ E_NOTICE);
	}

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
}

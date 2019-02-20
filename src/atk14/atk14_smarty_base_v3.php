<?php
/**
 * A middle layer for Smarty version 3.
 */
class Atk14SmartyDebug extends Smarty_Internal_Debug {

	function start_render($template) {
		parent::start_render($template);
		$this->smarty->start_template_render($template);
	}

	function end_render($template) {
		parent::end_render($template);
		$this->smarty->end_template_render($template);
	}

	function __construct($smarty) {
		$this->smarty = $smarty;
	}
}

class Atk14TemplateIndexItem implements ArrayAccess {
	function __construct($template, $parent) {
		$this->parent = $parent;
		$this->template = $template;
		$this->children = [];
	}

	function push($template) {
		$item = new Atk14TemplateIndexItem($template, $this);
		$this->children[] = $item;
		return $item;
	}

	public function offsetExists($o) {
		return true;
	}

	public function offsetGet($offset) {
		return $this->$offset;
	}

	public function offsetSet($key, $val) {
		throw Exception("Not supported");
	}

	public function offsetUnset($key) {
		throw Exception("Not supported");
	}
}

class Atk14TemplateIndex implements IteratorAggregate {

	function __construct() {
		$this->root = $this->actual = new Atk14TemplateIndexItem(null, null);
	}

	function enter($template) {
		$this->actual = $this->actual->push($template);
	}

	function leave() {
		$this->actual = $this->actual->parent;
	}

	function getIterator() {
		return new ArrayIterator($this->root->children);
	}
}


class Atk14SmartyBase extends SmartyBC{

	static $ATK14_RENDERED_TEMPLATES;

  public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true) {
		$d = $this->_debug;
		$out = parent::createTemplate($template, $cache_id, $compile_id, $parent, $do_clone);
		$this->_debug = $d;
		return $out;
	}

	function __construct(){
		parent::__construct();
		if( DEVELOPMENT ) {
			$this->debugging = true;
			$this->_debug = new Atk14SmartyDebug($this);
		}
		$this->setErrorReporting(E_ALL ^ E_NOTICE);
	}

	function start_template_render($template) {
		$template_fullpath = substr($template->source->filepath, strlen(realpath(__DIR__ . '/../../..')));
		self::$ATK14_RENDERED_TEMPLATES->enter($template_fullpath);
	}

	function end_template_render($template) {
		self::$ATK14_RENDERED_TEMPLATES->leave();
	}
}

Atk14SmartyBase::$ATK14_RENDERED_TEMPLATES = new Atk14TemplateIndex();

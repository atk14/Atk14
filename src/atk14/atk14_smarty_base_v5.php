<?php
/**
 * A middle layer for Smarty version 5.
 */
class Atk14SmartyBase extends Smarty\Smarty{

	static $ATK14_RENDERED_TEMPLATES;

	function __construct(){
		parent::__construct();
		if( DEVELOPMENT ) {
			$this->debugging = true;
			$this->_debug = new Atk14SmartyDebug($this);
		}
		$this->setErrorReporting(E_ALL & ~E_WARNING & ~E_NOTICE);

		parent::__construct();

		//	Using native PHP-functions or userland functions in your templates
		//	
		//	You can no longer use native PHP-functions or userland functions in your templates without registering them. If you need a function in your templates, register it first.
		//	The easiest way to do so is as follows:
		//	
		//	// native PHP functions used as modifiers need to be registered
		//	$smarty->registerPlugin('modifier', 'substr', 'substr');
		foreach([
			"array_slice",
			"defined",
			"get_class",
			"is_a",
			"is_null",
			"is_string",
			"strstr",
		] as $fn){
			$this->registerPlugin("modifier", $fn, $fn);
		}
	}

	public function createTemplate($template_name, $cache_id = null, $compile_id = null, $parent = null): Smarty\Template {
		$d = $this->_debug;
		$out = parent::createTemplate($template_name, $cache_id, $compile_id, $parent);
		$this->_debug = $d;
		return $out;
	}

	function start_template_render($template) {
		$template_fullpath = substr($template->source->filepath, strlen(realpath(__DIR__ . '/../../../')) + 1); // +1 cuts off the leading slash: "/app/view/main/index.tpl" -> "app/view/main/index.tpl"
		self::$ATK14_RENDERED_TEMPLATES->enter($template_fullpath);
	}

	function end_template_render($template) {
		self::$ATK14_RENDERED_TEMPLATES->leave();
	}

	public function getPluginsDir(){
		return [];
	}

	public function setPluginsDir($plugins_dir){
		foreach($plugins_dir as $dir){
			$this->addPluginsDir($dir);
		}
	}

	public function addPluginsDir($plugins_dir){
		// An user deprecated error is triggered in parent::addPluginsDir() (Smarty 5.4.3)
		return @parent::addPluginsDir($plugins_dir);
	}

	public function assignByRef($tpl_var, $value){
		return $this->assign($tpl_var, $value);
	}

	public function registerPlugin($type, $name, $callback, $cacheable = true) {
		if (isset($this->registered_plugins[$type][$name])) {
			// throw new Exception("Plugin tag '{$name}' already registered");
			$this->registered_plugins[$type][$name] = [$callback, (bool)$cacheable];
		} elseif (!is_callable($callback) && !class_exists($callback)) {
			throw new Exception("Plugin '{$name}' not callable");
		} else {
			$this->registered_plugins[$type][$name] = [$callback, (bool)$cacheable];
		}
		return $this;
	}
}

class Atk14SmartyDebug extends Smarty\Debug {

	var $smarty;

	function start_render(Smarty\Template $template, $mode = null) {
		parent::start_render($template, $mode);
		$this->smarty->start_template_render($template);
	}

	function end_render(Smarty\Template $template) {
		parent::end_render($template);
		$this->smarty->end_template_render($template);
	}

	function __construct($smarty) {
		$this->smarty = $smarty;
	}
}

class Atk14TemplateIndexItem implements ArrayAccess {

	var $parent;
	var $template;
	var $children;

	function __construct($template, $parent) {
		$this->parent = $parent;
		$this->template = $template;
		$this->children = array();
	}

	function push($template) {
		$item = new Atk14TemplateIndexItem($template, $this);
		$this->children[] = $item;
		return $item;
	}

	#[\ReturnTypeWillChange]
	public function offsetExists($o) {
		return true;
	}

	#[\ReturnTypeWillChange]
	public function offsetGet($offset) {
		return $this->$offset;
	}

	#[\ReturnTypeWillChange]
	public function offsetSet($key, $val) {
		throw Exception("Not supported");
	}

	#[\ReturnTypeWillChange]
	public function offsetUnset($key) {
		throw Exception("Not supported");
	}
}

class Atk14TemplateIndex implements IteratorAggregate {

	var $root;
	var $actual;

	function __construct() {
		$this->root = $this->actual = new Atk14TemplateIndexItem(null, null);
	}

	function enter($template) {
		$this->actual = $this->actual->push($template);
	}

	function leave() {
		$this->actual = $this->actual->parent;
	}

	#[\ReturnTypeWillChange]
	function getIterator() {
		return new ArrayIterator($this->root->children);
	}
}

Atk14SmartyBase::$ATK14_RENDERED_TEMPLATES = new Atk14TemplateIndex();

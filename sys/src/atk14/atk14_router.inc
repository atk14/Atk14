<?php
class Atk14Router{
	var $namespace = "";
	var $controller = "";
	var $action = "";
	var $lang = "";

	var $params = null;

	var $default_lang = "";

	var $page_title = "";
	var $page_description = "";

	var $redirected_to = null;

	function Atk14Router(){
		global $ATK14_GLOBAL;
		$this->default_lang = $ATK14_GLOBAL->getDefaultLang();
	}

	function recognizeUri($uri,$params,$namespace){
		global $ATK14_GLOBAL;
		$this->action = $this->controller = null;
		$this->lang = $ATK14_GLOBAL->getDefaultLang();
		$this->namespace = $namespace;
		$this->redirected_to = null;

		$out = $this->recognize($uri);
		if($this->controller && $this->action){
			// converting objects to their scalar values
			foreach($this->params->keys() as $k){
				if(is_object($o = $this->params->g($k))){ $this->params->s($k,$o->getId()); }
			}
		}
		return $out;
	}

	/**
	 * Consider an URI like this:
	 * 	/bookstore/admin/book/123-hobbit?print=true
	 * where "bookstore" is base href and "admin" is namespace.
	 * 
	 * Then the $uri will be
	 * 	/book/123-hobbit
	 * 
	 * It means no base href nor namespace nor params (the part after question mark; params are accessible through $this->params)
	 *
	 * When the $uri is beeing recognized, attributes $this->controller and $this->action must be set - otherwise leave them untouched.
	 *
	 * @abstract
	 */
	function recognize($uri){
		// must be covered by the descendent...
	}

	function buildLink($params){
		if(is_array($params)){ $params = new Dictionary($params); }
		foreach(array("namespace","controller","action","lang") as $k){
			$this->$k = $params->g($k);
			$params->delete($k);
		}
		$this->params = $params;
		foreach($this->params->keys() as $k){
			if(is_object($this->params->g($k))){ $this->params->s($k,$this->params->g($k)->getId()); }
		}
		return $this->build();
	}

	/**
	 * 
	 * 
	 * @abstract
	 */
	function build(){

	}

	/**
	 * 
	 */
	function _redirect_to($new_uri){
		global $ATK14_GLOBAL;

		$new_uri = preg_replace('/^\//','',$new_uri);

		$base_href = $ATK14_GLOBAL->getBaseHref();
		$namespace = $this->namespace ? $this->namespace."/" : "";

		$this->redirected_to = $base_href.$namespace.$new_uri.Atk14Url::EncodeParams($this->params,array("connector" => "&"));
	}

	function _not_found(){
		$this->controller = "application";
		$this->action = "error404";
	}
}

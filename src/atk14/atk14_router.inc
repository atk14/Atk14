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
		$this->action = $this->controller = null;
		$this->lang = $this->default_lang;
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
	 * Consider an URI like this:
	 * 	/bookstore/admin/book/123-hobbit?print=true
	 * where "bookstore" is base href and "admin" is namespace.
	 * 
	 * Then the $uri will be
	 * 	/book/123-hobbit
	 * 
	 * It means there's no base href, namespace nor params (the part after question mark; params are accessible through $this->params)
	 *
	 * When the $uri is beeing recognized, attributes $this->controller and $this->action must be set - otherwise leave them untouched.
	 *
	 * @abstract
	 */
	function recognize($uri){
		// must be covered by the descendent...
	}


	/**
	 * 
	 * 
	 * @abstract
	 */
	function build(){
		// must be covered by the descendent...
	}

	/**
	 * Give a new URI on which you want to redirect.
	 *  
	 * Do not mention base href, namespace nor parameters.
	 * 
	 * <code>
	 *	$this->_redirect_to("/book/123-hobbit-or-there-and-back-again"); // redirecting from the previous title "/book/123-hobbit"
	 * </code>
	 * 
	 */
	function _redirect_to($new_uri){
		global $ATK14_GLOBAL;

		$new_uri = preg_replace('/^\//','',$new_uri);

		$base_href = $ATK14_GLOBAL->getBaseHref();
		$namespace = $this->namespace ? $this->namespace."/" : "";

		$this->redirected_to = $base_href.$namespace.$new_uri.Atk14Url::EncodeParams($this->params,array("connector" => "&"));
	}

	/**
	 * Useful within the recognize() method in case when you expect something and you don't find it.
	 *
	 * <code>
	 *  if(!$book = Book::GetInstanceById($this->params->getInt("id"))){
	 *		$this->_not_found();
	 *		return;
	 *	}
	 *	</code>
	 */
	function _not_found(){
		$this->controller = "application";
		$this->action = "error404";
	}
}

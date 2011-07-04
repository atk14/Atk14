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

	var $redirected_to = "";

	function Atk14Router(){
		global $ATK14_GLOBAL;
		$this->default_lang = $ATK14_GLOBAL->getDefaultLang();
	}

	function recognize($uri,$params,$namespace){
		global $ATK14_GLOBAL;
		$this->action = $this->controller = null;
		$this->lang = $ATK14_GLOBAL->getDefaultLang();
		$this->namespace = $namespace;
		$this->redirected_to = "";

		return $this->_recognize($uri);
	}

	function build($params){
		if(is_array($params)){ $params = new Dictionary($params); }
		foreach(array("namespace","controller","action","lang") as $k){
			$this->$k = $params->g($k);
			$params->delete($k);
		}
		$this->params = $params;
		return $this->_build();
	}

	function _redirect_to($new_uri){

	}
}

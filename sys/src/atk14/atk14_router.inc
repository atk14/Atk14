<?php
class Atk14Router{
	var $namespace = "";
	var $controller = "";
	var $action = "";
	var $lang = "";

	var $params = null;


	var $default_lang = "";

	function Atk14Router(){
		global $ATK14_GLOBAL;
		$this->default_lang = $ATK14_GLOBAL->getDefaultLang();
		$this->params = new Dictionary();
	}

	function recognize($uri,$params){
		
	}

	function build($params){
		
	}
}

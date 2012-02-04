<?php
class Atk14StaticRouter extends Atk14Router{

	var $namespace = "";

	function __construct(){
		parent::__construct();

		$this->setUp();
	}

	function setUp(){

	}

	function recognize(){ }
	function build(){ }

	/**
	 *	$this->addRoute("/",array("path" => "main/index"));
	 *	$this->addRoute("/","main/index");
	 *
	 *	$this->addRoute("/book/<slug>-<id>","book/detail",array(
	 *		"slug" => '/[a-z0-9-]+/',
	 *		"id" => '/[0-9]+/'
	 *	));
	 *	$this->addRoute("/book/<slug>-<id>",array(
	 *		"path" => "book/detail",
	 *		"params" => array(
	 *			"slug" => '/[a-z0-9-]+/',
	 *			"id" => '/[0-9]+/'
	 *		)
	 *	);
	 */ 
	function addRoute($uri,$options = array(),$params = array()){
		global $ATK14_GLOBAL;

		if(is_string($options)){
			$options = array("path" => $options);
		}

		$options = array_merge(array(
			"lang" => $this->default_lang,
			"namespace" => $this->namespace,
			"path" => null,
			"params" => $params,
			"title" => null,
			"description" => null,
		),$options);

		// tady rozsekame path podle nektereho ze vzoru:
		//
		//		namespace/lang/controller/action
		//		lang/controller/action
		//		controller/action
		//
		if(isset($options["path"])){
			$options["path"] = preg_replace('/^\/?(.*?)\/?$/','\1',$options["path"]); // "/main/index/" -> "main/index"
			$ar = split('/',$options["path"]);
			switch(sizeof($ar)){
				case "4":
					$options["namespace"] = array_shift($ar);
				case "3":
					$options["lang"] = array_shift($ar);
				case "2":
					$options["controller"] = array_shift($ar);
					$options["action"] = array_shift($ar);
					unset($options["path"]);
			}
		}

		$routes_key = "routes". ($options["namespace"] ? "[$options[namespace]]" : ""); // "routes", "routes[admin]"...
		$routes = &$ATK14_GLOBAL->getValueRef($routes_key,array());

		$recipe = $options["params"];

		foreach(array(
			"path" => "__path__",
			"title" => "__page_title__",
			"description" => "__page_description__",
			"controller" => "controller",
			"action" => "action",
			"lang" => "lang",
		) as $nice_key => $orig_key){
			if(isset($options[$nice_key])){
				$recipe[$orig_key] = $options[$nice_key];
			}
		}

		if(preg_match('/<lang>/',$uri)){ unset($recipe["lang"]); }

		$recipe["__omit_trailing_slash__"] = !preg_match('/\/$/',$uri);
		$uri = preg_replace('/^\/?(.*?)\/?$/','\1',$uri); // "/books/" -> "books"; "/" -> ""

		$routes[$uri] = $recipe;
	}
}

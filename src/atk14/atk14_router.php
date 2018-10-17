<?php
/**
 * Class for processing specific (or nice) URLs.
 *
 * @filesource
 */
/**
 * Class for processing specific (or nice) URLs.
 *
 * A router should have basically two methods, {@link Atk14Router::recognize()} and {@link Atk14Router::build()}.
 *
 * {@link Atk14Router::recognize()} method should examine current url and setup parameters to tell the {@link Atk14Dispatcher} which action in which controller should be executed
 * and with which parameters.
 *
 * {@link Atk14Router::build()} method does the reverse action. By current controller, action etc it generates a URL where should be this resource accessible.
 *
 * @package Atk14\Core
 */
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

	function __construct($options = array()){
		global $ATK14_GLOBAL;
		$options += array(
			"namespace" => null,
		);

		if(isset($options["namespace"])){
			$this->namespace = $options["namespace"];
		}

		$this->default_lang = $ATK14_GLOBAL->getDefaultLang();

		$this->setUp();
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
			if(is_object($this->params->g($k))){ $this->params->s($k,Atk14Utils::ToScalar($this->params->g($k))); }
		}
		return $this->build();
	}

	/**
	 * Method used to examine applications url.
	 *
	 * When the application uses specific urls, this method should break up the url into pieces and setup needed parameters.
	 * It means namespace, controller, language, action and query parameters.
	 * Controller and action are mandatory.
	 *
	 * Consider an URI like this:
	 * ```
	 * /bookstore/admin/book/123-hobbit?print=true
	 * ```
	 * where "bookstore" is base href and "admin" is namespace.
	 *
	 * Then the $uri will be
	 * ```
	 * /book/123-hobbit
	 * ```
	 *
	 * It means there's no base href, namespace nor params (the part after question mark; params are accessible through $this->params)
	 * After this method is used, attributes $this->controller and $this->action must be set - otherwise leave them untouched.
	 *
	 * @abstract
	 * @param string $uri URI to be examined
	 */
	function recognize($uri){
		// may be covered by a descendent...
	}


	/**
	 * 
	 * 
	 */
	function build(){
		// may be covered by a descendent...
	}

	function setUp(){
		// may be covered by a descendent...
	}

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
			$ar = explode('/',$options["path"]);
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

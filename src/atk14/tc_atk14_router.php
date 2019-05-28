<?php
/**
 * Base class for routers testing
 *
 * <code>
 *	<?php
 *	// file: test/routers/tc_articles_router.php
 *	class TcArticlesRouter extends TcAtk14Router {
 *		function test(){
 *			$uri = $this->assertBuildable([
 *				"controller" => "articles",
 *				"action" => "detail",
 *				"id" => 123,
 *			]);
 *			$this->assertEquals("/article/very-nice-article/",$uri);
 *		}
 *	}
 * </code>
 */
class TcAtk14Router extends TcAtk14Base {

	var $router = null;

	/**
	 *
	 *	$uri = $this->assertBuildable([
	 *		"controller" => "articles",
	 *		"action" => "detail",
	 *		"id" => 123,
	 *	]);
	 *	$this->assertEquals("/article/very-nice-article/",$uri);
	 */
	function assertBuildable($params = array(),&$ret_params = null){
		$uri = $this->_build($params,$ret_params);
		$this->assertNotNull($uri);

		return $uri;
	}

	function assertNotBuildable($params = array()){
		$uri = $this->_build($params);
		$this->assertNull($uri);
	}

	function assertRecognizable($uri,&$params = array()){
		$params = array();
		$this->_recognize($uri,$params);

		$this->assertTrue(!is_null($this->router->controller) && !is_null($this->router->action),$uri);
		
		return array(
			"namespace" => $this->router->namespace,
			"controller" => $this->router->controller,
			"action" => $this->router->action,
			"lang" => $this->router->lang,
		);
	}

	function assertNotRecognizable($uri,$params = array()){
		$this->_recognize($uri,$params);

		$this->assertTrue(is_null($this->router->controller) && is_null($this->router->action),$uri);
	}

	function _build($params,&$ret_params = null){
		global $ATK14_GLOBAL;

		$ret_params = null;

		foreach(array("namespace","controller","action","lang") as $key){
			$val = array_key_exists($key,$params) ? (string)$params[$key] : $ATK14_GLOBAL->getValue($key);
			$this->router->$key = $val;
			unset($params[$key]);
		}

		$params = new Dictionary($params);
		$this->router->params = $params;

		$uri = $this->router->build();
		$ret_params = $this->router->params;

		return $uri;
	}

	function _recognize($uri,&$params){
		$array_given = false;
		if(!is_object($params)){
			$array_given = true;
			$params = new Dictionary($params);
		}

		foreach(array("namespace","controller","action","lang") as $key){
			$this->router->$key = null;
		}
		$this->router->params = $params;

		$this->router->recognize($uri);

		$params = $this->router->params;
		if($array_given){
			$params = $params->toArray();
		}
	}
}

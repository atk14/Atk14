<?php
/**
 * Class that simulates client browser.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Class that simulates client browser.
 *
 * This class is suitable for testing controllers.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @todo Some more explanation
 */
class Atk14Client{
	
	/**
	 * @var Atk14Session
	 */
	var $session = null;

	/**
	 * @var Atk14Flash
	 */
	var $flash = null;

	/**
	 * @var ApplicationController
	 */
	var $controller = null;

	var $namespace = "";

	var $_UserAgent = "Atk14 Testing Client";

	var $_RemoteAddr = "0.0.0.0";

	var $_BasicAuthUsername = null;
	var $_BasicAuthPassword = null;

	/**
	 *
	 */
	function Atk14Client(){
		$this->session = &Atk14Session::GetInstance();
		$this->flash = &Atk14Flash::GetInstance();

		if(!isset($GLOBALS["_COOKIE"])){ $GLOBALS["_COOKIE"] = array(); }
		$GLOBALS["_SERVER"]["REMOTE_ADDR"] = "0.0.0.0";
		$GLOBALS["_COOKIE"][SESSION_STORER_COOKIE_NAME_CHECK] = "1";
	}

	/**
	 * Disables cookies.
	 */
	function disableCookies(){
		$GLOBALS["_COOKIE"] = array();
	}

	/**
	 * <code>
	 * $client->setBasicAuth("robin","the hooded man");
	 * </code>
	 */
	function setBasicAuth($username,$password){
		$this->_BasicAuthUsername = $username;
		$this->_BasicAuthPassword = $password;
	}

	/**
	 * Sends a GET request.
	 *
	 * <code>
	 * $controller = $client->get("books/index");
	 * $controller = $client->get("books/index",array("q" => "Mark Twain"));
	 * $controller = $client->get("en/books/index"); // with language
	 * $controller = $client->get("admin/en/books/index"); // with namespace
	 * </code>
	 *
	 * @param string $path
	 * @param array $params
	 * @return ApplicationController
	 */
	function get($path,$params = array()){
		return $this->_doRequest("GET",$path,array("params" => $params));
	}

	/**
	 * Sends a POST request.
	 * 
	 * <code>
	 * $client->post("books/edit",array(
	 *	"id" => 123,
	 *	"title" => "A New Title"
	 * ));
	 * 
	 * // sending raw data
	 * $code->post("images/create_new",$binary_image_content,array("content_type" => "image/jpg"));
	 *
	 * </code>
	 *
	 * @param string $path
	 * @param array $params
	 * @return ApplicationController
	 */
	function post($path,$params = array(),$options = array()){
		if(!is_array($params)){
			$options["raw_post_data"] = $params;
			$params = array();
		}else{
			$options["params"] = $params;
		}
		return $this->_doRequest("POST",$path,$options);
	}

	/**
	 * Common method to make HTTP requests
	 * @access private
	 */
	function _doRequest($method,$path,$options = array()){
		global $ATK14_GLOBAL;

		$options = array_merge(array(
			"params" => array(),
			"raw_post_data" => null,
			"content_type" => null,
		),$options);

		$params = $options["params"];

		$request = new HTTPRequest();
		$request->setUserAgent($this->_UserAgent);
		$request->setRemoteAddr($this->_RemoteAddr);

		if($options["content_type"]){
			$request->setContentType($options["content_type"]);
		}

		if(isset($options["raw_post_data"])){
			$request->setRawPostData($options["raw_post_data"]);
		}

		if($this->_BasicAuthUsername){ $request->setBasicAuthUsername($this->_BasicAuthUsername); }
		if($this->_BasicAuthPassword){ $request->setBasicAuthPassword($this->_BasicAuthPassword); }

		$this->flash->reset();

		// converting objects to scalars
		foreach($params as &$v){
			if(is_object($v)){ $v = $v->getId(); }
		}

		$namespace = $this->namespace;
		$lang = $ATK14_GLOBAL->getDefaultLang();

		$path_ar = explode("/",$path);

		if(sizeof($path_ar)==2){
			list($controller,$action) = $path_ar;
		}
		if(sizeof($path_ar)==3){
			list($lang,$controller,$action) = $path_ar;
		}
		if(sizeof($path_ar)==4){
			list($namespace,$lang,$controller,$action) = $path_ar;
		}

		$request->setMethod($method);
		if($method=="POST"){
			$request->setPostVars($params);
		}
		if($method=="GET"){
			$request->setGetVars($params);
		}
		$request->setUri(Atk14Url::BuildLink(array(
			"namespace" => $namespace,
			"action" => $action,
			"controller" => $controller,
			"lang" => $lang
		),array("connector" => "&")));

		$ctrl = Atk14Dispatcher::Dispatch(array(
			"display_response" => false,
			"request" => $request,
			"return_controller" => true
		));

		$this->controller = $ctrl;

		return $ctrl;
	}

	/**
	 * Gets content from response.
	 *
	 * @return string response content
	 */
	function getContent(){
		return $this->controller->response->buffer->toString();
	}

	function getContentType(){
		return $this->controller->response->getContentType();
	}

	function getLocation(){
		return $this->controller->response->getLocation();
	}

	function redirected(){
		return $this->controller->response->redirected();
	}

	/**
	 * Gets status code of a server response (e.g. 200)
	 *
	 * @return int HTTP status code
	 */
	function getStatusCode(){
		return $this->controller->response->getStatusCode();
	}

	/**
	 * Gets status message of a server response (e.g. "Found").
	 */
	function getStatusMessage(){
		return $this->controller->response->getStatusMessage();
	}

	function setUserAgent($user_agent){
		return $this->_UserAgent = $user_agent;
	}

	function setRemoteAddr($addr){
		$this->_RemoteAddr = $addr;
	}
}

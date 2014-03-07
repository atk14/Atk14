<?php
/**
 * Class that simulates client browser.
 *
 * @filesource
 */

/**
 * Class that simulates client browser.
 *
 * This class is suitable for testing controllers.
 *
 * Example of testing a GET request
 *
 * 	$controller = $this->client->get("customers/index");
 * 	$this->assertEquals(200, $this->client->getStatusCode());
 * 	$this->assertNotNull($finder = $controller->tpl_data["finder"]);
 * 	$this->assertTrue(sizeof($finder->getRecords())>0);
 *
 * Example of testing a POST request
 *
 * 	$controller = $this->client->post("logins/sign_in", array("username" => "admin", "password" => "SeCrEt.P4ssw0rD"));
 * 	$this->assertEquals(200, $this->client->getStatusCode());
 * 	$this->assertEquals(1, $controller->session->getValue("admin_id"));
 *
 * @package Atk14\Core
 * @todo Some more explanation
 */
class Atk14Client{
	
	/**
	 * Instance of session storer.
	 *
	 * @var Atk14Session
	 * @see Atk14Session
	 */
	var $session = null;

	/**
	 * Instance of Atk14Flash.
	 *
	 * The flash contains messages to be displayed by clients browser
	 *
	 * @var Atk14Flash
	 * @see Atk14Flash
	 */
	var $flash = null;

	/**
	 * Instance of Atk14Controller used by current request.
	 *
	 * @var ApplicationController
	 * @see Atk14Controller
	 */
	var $controller = null;

	/**
	 * Current controllers namespace.
	 *
	 * @var string
	 */
	var $namespace = "";

	/**
	 * Content sent in User-Agent HTTP header.
	 *
	 * @var string
	 * @ignore
	 */
	var $_UserAgent = "Atk14 Testing Client";

	/**
	 * @ignore
	 */
	var $_RemoteAddr = "0.0.0.0";

	/**
	 * Basic authentication username.
	 *
	 * @var string
	 * @ignore
	 */
	var $_BasicAuthUsername = null;

	/**
	 * Basic authentication password.
	 *
	 * @var string
	 * @ignore
	 */
	var $_BasicAuthPassword = null;

	/**
	 * Constructor
	 */
	function Atk14Client(){
		global $ATK14_GLOBAL;
		$this->session = $ATK14_GLOBAL->getSession();
		$this->flash = Atk14Flash::GetInstance();

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
	 * Set basic HTTP authentization values.
	 *
	 * <code>
	 * $client->setBasicAuth("robin","the hooded man");
	 * </code>
	 *
	 * @param string $username
	 * @param string $password
	 */
	function setBasicAuth($username,$password){
		$this->setBasicAuthUsername($username);
		$this->setBasicAuthPassword($password);
	}

	function getBasicAuthUsername(){ return $this->_BasicAuthUsername; }
	function setBasicAuthUsername($username){ $this->_BasicAuthUsername = $username; }

	function getBasicAuthPassword(){ return $this->_BasicAuthPassword; }
	function setBasicAuthPassword($password){ $this->_BasicAuthPassword = $password; }

	function getBasicAuthString(){
		$username = $this->getBasicAuthUsername();
		$password = $this->getBasicAuthPassword();
		if(strlen($username)>0 || strlen($password)>0){
			return "$username:$password";
		}
	}

	function setBasicAuthString($string){
		if(preg_match('/^(.*?):(.*)/',$string,$matches)){
			$this->setBasicAuthUsername($matches[1]);
			$this->setBasicAuthPassword($matches[2]);
			return;
		}

		$this->setBasicAuthUsername(null);
		$this->setBasicAuthPassword(null);
	}

	/**
	 * Sends a GET request.
	 *
	 * Example
	 *
	 * {{{
	 * 	$controller = $client->get("books/index");
	 * 	$controller = $client->get("books/index",array("q" => "Mark Twain"));
	 * }}}
	 *
	 * With language
	 *
	 * {{{
	 * 	$controller = $client->get("en/books/index");
	 * }}}
	 *
	 * With namespace
	 *
	 * {{{
	 * 	$controller = $client->get("admin/en/books/index");
	 * }}}
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
	 * @param array $options
	 * <ul>
	 * 	<li>raw_post_data</li>
	 * 	<li>content_type</li>
	 * </ul>
	 *
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
	 * $client->makeRequest("GET","articles/detail",array("id" => 123)); // same as $client->get("articles/detail",array("id" => 123))
	 * $client->makeRequest("DELETE","articles/destroy",array("id" => 123));
	 */
	function makeRequest($method,$path,$params = array(),$options = array()){
		$method = strtoupper($method);
		if($method=="POST"){
			return $this->post($path,$params,$options);
		}
		return $this->_doRequest($method,$path,$params,$options);
	}

	/**
	 * Common method to make HTTP requests
	 *
	 * @ignore
	 */
	private function _doRequest($method,$path,$options = array()){
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
		$request->setHttpHost($ATK14_GLOBAL->getHttpHost());
		$request->setServerPort(80);

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
		}else{
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

	/**
	 * Content of Content-Type HTTP header
	 *
	 * @return string
	 */
	function getContentType(){
		return $this->controller->response->getContentType();
	}

	/**
	 * Returns redirection of the request
	 *
	 * @return string content of Location response header.
	 */
	function getLocation(){
		return $this->controller->response->getLocation();
	}

	/**
	 * Checks if the request was redirected
	 *
	 * @return bool true when request was redirected, otherwise false
	 */
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
	 *
	 * @return string
	 */
	function getStatusMessage(){
		return $this->controller->response->getStatusMessage();
	}

	/**
	 * Sets string for User-Agent header.
	 *
	 * @param string $user_agent
	 * @return string
	 */
	function setUserAgent($user_agent){
		return $this->_UserAgent = $user_agent;
	}

	/**
	 * Sets ip address of client.
	 *
	 * @param string $addr
	 */
	function setRemoteAddr($addr){
		$this->_RemoteAddr = $addr;
	}
}

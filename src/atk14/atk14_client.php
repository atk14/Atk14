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
 * ```
 * $controller = $this->client->get("customers/index");
 * $this->assertEquals(200, $this->client->getStatusCode());
 * $this->assertNotNull($finder = $controller->tpl_data["finder"]);
 * $this->assertTrue(sizeof($finder->getRecords())>0);
 * ```
 *
 * Example of testing a POST request
 *
 * ```
 * $controller = $this->client->post("logins/sign_in", array("username" => "admin", "password" => "SeCrEt.P4ssw0rD"));
 * $this->assertEquals(200, $this->client->getStatusCode());
 * $this->assertEquals(1, $controller->session->getValue("admin_id"));
 * ```
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
	 * Store for cookies
	 *
	 * @var ignore
	 */
	var $_Cookies = array();

	/**
	 * Flag whether cookies are enabled
	 *
	 * @var boolean
	 */
	var $_CookiesEnabled = true;

	/**
	 * Constructor
	 */
	function __construct(){
		global $ATK14_GLOBAL;
		$this->session = $ATK14_GLOBAL->getSession();
		$this->flash = Atk14Flash::GetInstance();

		$GLOBALS["_SERVER"]["REMOTE_ADDR"] = "0.0.0.0";
		$this->setCookie(SESSION_STORER_COOKIE_NAME_CHECK,"1");
	}

	/**
	 * Disables cookies.
	 *
	 * <code>
	 *	$client->setCookie("cookie_1","val");
	 * 	echo sizeof($client->getCookies()); // 1
	 *	//
	 * 	$client->disableCookies();
	 * 	echo sizeof($client->getCookies()); // 0
	 *	$client->setCookie("cookie_2","val");
	 * 	echo sizeof($client->getCookies()); // 0
	 * </code>
	 */
	function disableCookies(){
		$this->_CookiesEnabled = false;
	}

	/**
	 * Enables cookies.
	 */
	function enableCookies(){
		$this->_CookiesEnabled = true;
	}

	/**
	 *
	 * <code>
	 *	var_dump($client->getCookies()); // array("cookie1" => "value")
	 * </code>
	 */
	function getCookies(){
		if(!$this->_CookiesEnabled){ return array(); }
		return $this->_Cookies;
	}

	/**
	 *
	 * <code>
	 *	if($client->cookiesEnabled()){
	 *		// cookies are enabled
	 *	}
	 * </code>
	 */
	function cookiesEnabled(){ return $this->_CookiesEnabled; }

	/**
	 *
	 * <code>
	 *	$client->setCookies(array(
	 *		"check" => "1"
	 *		"cookie1" => "value",
	 *	);
	 * </code>
	 */
	function setCookies($cookies){
		if(!$this->cookiesEnabled()){ return false; }
		$this->_Cookies = $cookies;
		return true;
	}

	/**
	 *
	 * <code>
	 *	$client->setCookie("cookie1","value");
	 * </code>
	 */
	function setCookie($name,$value){
		if(!$this->cookiesEnabled()){ return false; }
		$this->_Cookies[$name] = $value;
		return true;
	}

	/**
	 *
	 * <code>
	 *	$client->clearCookie("cookie1");
	 * </code>
	 */
	function clearCookie($name){
		unset($this->_Cookies[$name]);
	}

	/**
	 * Cleares all cookies
	 *
	 * It's not depend on the cookies-enabled flag.
	 */
	function clearCookies(){
		$this->_Cookies = array();
	}

	/**
	 * Set basic HTTP authentization values.
	 *
	 * ```
	 * $client->setBasicAuth("robin","the hooded man");
	 * ```
	 *
	 * @param string $username
	 * @param string $password
	 */
	function setBasicAuth($username,$password){
		$this->setBasicAuthUsername($username);
		$this->setBasicAuthPassword($password);
	}

	/**
	 * Get username set for basic authentization.
	 *
	 * @return string
	 */
	function getBasicAuthUsername(){ return $this->_BasicAuthUsername; }

	/**
	 * Set username for basic authentization.
	 *
	 * @param string $username
	 */
	function setBasicAuthUsername($username){ $this->_BasicAuthUsername = $username; }

	/**
	 * Get password set for basic authentization.
	 *
	 * @return string
	 */
	function getBasicAuthPassword(){ return $this->_BasicAuthPassword; }

	/**
	 * Set password for basic authentization.
	 *
	 * @param string $password
	 */
	function setBasicAuthPassword($password){ $this->_BasicAuthPassword = $password; }

	/**
		* Return complete string for basic authentication
		*
		* @return string
	 */
	function getBasicAuthString(){
		$username = $this->getBasicAuthUsername();
		$password = $this->getBasicAuthPassword();
		if(strlen($username)>0 || strlen($password)>0){
			return "$username:$password";
		}
	}

	/**
	 * Set username and password for basic authentication.
	 *
	 * Both values are sent together separated by colon.
	 * @param string $string
	 *
	 */
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
	 * ```
	 * $controller = $client->get("books/index");
	 * $controller = $client->get("books/index",array("q" => "Mark Twain"));
	 * ```
	 *
	 * If you are calling this from tc_books.php file, you can use:
	 * ```
	 * $controller = $client->get("index",array("q" => "We Are All Legends"));
	 * ```
	 *
	 * With language specification
	 * ```
	 * $controller = $client->get("en/books/index");
	 * ```
	 *
	 * With namespace
	 * ```
	 * $controller = $client->get("admin/en/books/index");
	 * ```
	 *
	 * @param string $path
	 * @param array $params
	 * @return Atk14Controller
	 * @see makeRequest() $options description
	 */
	function get($path,$params = array()){
		return $this->_doRequest("GET",$path,array("params" => $params));
	}

	/**
	 * Sends a POST request.
	 *
	 * ```
	 * $client->post("books/edit",array(
	 * 	"id" => 123,
	 * 	"title" => "A New Title"
	 * ));
	 * ```
	 *
	 * Sending raw data
	 * ```
	 * $code->post("images/create_new",$binary_image_content,array(
	 * 	"content_type" => "image/jpg"
	 * ));
	 * ```
	 *
	 * @param string $path
	 * @param array $params
	 * @param array $options
	 *
	 * @return Atk14Controller
	 * @see makeRequest() $options description
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
	 * Common method for making requests.
	 *
	 * Example
	 * ```
	 * $client->makeRequest("GET","articles/detail",array("id" => 123));
	 * ```
	 * is same as
	 * ```
	 * $client->get("articles/detail",array("id" => 123))
	 * ```
	 * Another - DELETE request
	 * ```
	 * $client->makeRequest("DELETE","articles/destroy",array("id" => 123));
	 * ```
	 *
	 * @param string $method HTTP method (GET, POST ...)
	 * @param string $path
	 * @param array $params query parameters
	 * @param array $options
	 * - raw_post_data
	 * - content_type
	 * @return Atk14Controller
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

		$GLOBALS["HTTP_RESPONSE"]->clearCookies(); // !! danger !! global variable manipulation

		$request = new HTTPRequest();
		$request->setUserAgent($this->_UserAgent);
		$request->setRemoteAddr($this->_RemoteAddr);
		$request->setHttpHost($ATK14_GLOBAL->getHttpHost());
		$request->setServerPort(80);
		$request->setCookieVars($this->getCookies());

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

		switch(sizeof($path_ar)){
			case 1:
				// "create_new"
				$action = $path_ar[0];
				// name of the controller gonna be determined by the filename of the tc_*.php file
 				// "/home/yarri/projects/lemonade/test/controllers/tc_password_recoveries.php" -> "password_recoveries"
				preg_match('/tc_([a-z0-9_]+)\.(inc|php)$/',$GLOBALS["_TEST"]["FILENAME"],$matches);
				$controller = $matches[1];
				break;

			case 2:
				// "sessions/create_new"
				list($controller,$action) = $path_ar;
				break;

			case 3:
				// "en/sessions/create_new"
				list($lang,$controller,$action) = $path_ar;
				break;

			case 4:
				// "api/en/sessions/create_new"
				list($namespace,$lang,$controller,$action) = $path_ar;
				break;

			default:
				throw new Exception("Invalid path to action: $path");
		}

		$request->setMethod($method);
		if($method=="POST"){
			$request->setPostVars($params);
			$request->setPostVar("_csrf_token_","testing_csrf_token"); // TODO: this is a nasty hack; originally this was in test/controller/tc_logins.php in method setUp()
		}else{
			$request->setGetVars($params);
		}
		$request->setUri(Atk14Url::BuildLink(array(
			"namespace" => $namespace,
			"action" => $action,
			"controller" => $controller,
			"lang" => $lang
		),array("connector" => "&")));

		$GLOBALS["HTTP_REQUEST"] = $request; // !! danger !! changing global variable manipulation

		$ctrl = Atk14Dispatcher::Dispatch(array(
			"display_response" => false,
			"request" => $request,
			"return_controller" => true
		));

		$this->controller = $ctrl;

		if($this->cookiesEnabled()){
			foreach($GLOBALS["HTTP_RESPONSE"]->getCookies() as $cookie){
				if(!$cookie->isDesignatedFor($request)){ continue; }
				if($cookie->isExpired()){
					$this->clearCookie($cookie->getName());
					continue;
				}
				$this->setCookie($cookie->getName(),$cookie->getValue());
			}
		}

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

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
 * $controller = $this->client->post("logins/sign_in", array(
 * 	"username" => "admin",
 * 	"password" => "SeCrEt.P4ssw0rD",
 * ));
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
	 * @var HTTPCookie[]
	 * @ignore
	 */
	var $_Cookies = array();

	/**
	 * Flag whether cookies are enabled
	 *
	 * @var boolean
	 * @ignore
	 */
	var $_CookiesEnabled = true;

	/**
	 * The most recent request
	 *
	 * @var HTTPRequest
	 * @ignore
	 */
	var $_RecentRequest = null;

	/**
	 * Constructor
	 */
	function __construct(){
		global $ATK14_GLOBAL;
		$this->session = $ATK14_GLOBAL->getSession();
		$this->flash = Atk14Flash::GetInstance();

		$GLOBALS["_SERVER"]["REMOTE_ADDR"] = "0.0.0.0";
		$this->addCookie(new HTTPCookie(SESSION_STORER_COOKIE_NAME_CHECK,"1"));
	}

	/**
	 * Disables cookies.
	 *
	 * ```
	 * $client->setCookie("cookie_1","val");
	 * echo sizeof($client->getCookies()); // 1
	 *
	 * $client->disableCookies();
	 * echo sizeof($client->getCookies()); // 0
	 * $client->setCookie("cookie_2","val");
	 * echo sizeof($client->getCookies()); // 0
	 * ```
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
	 * Tests if we have cookies enabled.
	 *
	 * ```
	 * if($client->cookiesEnabled()){
	 * 	// cookies are enabled
	 * }
	 * ```
	 *
	 * @return boolean
	 */
	function cookiesEnabled(){ return $this->_CookiesEnabled; }

	/**
	 * Adds a cookie.
	 *
	 * ```
	 * $client->addCookie(new HTTPCookie("cookie1","value"));
	 * ```
	 *
	 * @param HTTPCookie $cookie
	 */
	function addCookie($cookie){
		$this->_Cookies[] = $cookie;
	}

	/**
	 * Returns cookies valid for the given HTTP request
	 *
	 * ```
	 * var_dump($client->getCookies());
	 * ```
	 * returns array("cookie1" => "value")
	 *
	 * @param HTTPRequest $request
	 * @return array
	 */
	function getCookies($request = null){
		if(!$this->cookiesEnabled()){ return array(); }

		if(!$request){
			$request = $this->getRecentRequest() ? $this->getRecentRequest() : $GLOBALS["HTTP_REQUEST"];
		}

		$out = array();
		foreach($this->_Cookies as $cookie){
			if(!$cookie->isDesignatedFor($request)){ continue; }
			if($cookie->isExpired()){
				unset($out[$cookie->getName()]);
				continue;
			}
			$out[$cookie->getName()] = $cookie->getValue();
		}

		return $out;
	}

	/**
	 * Clears all cookies
	 *
	 * It's not dependent on the cookies-enabled flag.
	 */
	function clearCookies(){
		$this->_Cookies = array();
	}

	/**
	 * Set basic HTTP authentization values.
	 *
	 * ```
	 * $client->setBasicAuth("robin","theHoodedMan");
	 * $client->setBasicAuth("robin:theHoodedMan);
	 * ```
	 *
	 * @param string $username
	 * @param string $password
	 */
	function setBasicAuth($username,$password = ""){
		if($password === ""){
			$ary = explode(":",$username);
			if(sizeof($ary)>=2){
				$username = array_shift($ary);
				$password = join(":",$ary);
			}
		}
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
	 * $controller = $client->get("en/books/index",array("q" => "Mark Twain"));
	 * $controller = $client->get("amin/en/books/detail",array("id" => 123));
	 *
	 * // Real world URIs
	 * $controller = $client->get("/en/books/?q=Mark+Twain");
	 * $controller = $client->get("http://example.com/en/books/?q=Mark+Twain");
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
	 * // or
	 * $client->post("/en/books/edit/?id=123",array(
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
	 * $client->get("articles/detail",array("id" => 123));
	 * // or
	 * $client->get("/en/articles/detail/?id=123");
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

		// converting objects to scalars
		foreach($options["params"] as &$v){
			if(is_object($v)){ $v = $v->getId(); }
		}

		if($method=="POST"){
			$get_params = array();
			$post_params = $options["params"];
		}else{
			$get_params = $options["params"];
			$post_params = array();
		}

		$GLOBALS["HTTP_RESPONSE"]->clearCookies(); // !! danger !! global variable manipulation

		$request = clone($GLOBALS["HTTP_REQUEST"]);
		$request->setUserAgent($this->_UserAgent);
		$request->setRemoteAddr($this->_RemoteAddr);
		$request->setHttpHost($ATK14_GLOBAL->getHttpHost());
		$request->setServerPort(80);

		$cookies = $this->getCookies($request);
		$request->setCookieVars($this->getCookies($request));
		$GLOBALS["HTTP_REQUEST"]->setCookieVars($this->getCookies($request)); // !! danger !! global variable manipulation; currently this is needed for SessionStorer (I'm sorry)

		if($options["content_type"]){
			$request->setContentType($options["content_type"]);
		}

		if(isset($options["raw_post_data"])){
			$request->setRawPostData($options["raw_post_data"]);
		}

		if($this->_BasicAuthUsername){ $request->setBasicAuthUsername($this->_BasicAuthUsername); }
		if($this->_BasicAuthPassword){ $request->setBasicAuthPassword($this->_BasicAuthPassword); }

		$this->flash->reset();

		$namespace = $this->namespace;
		$lang = $ATK14_GLOBAL->getDefaultLang();

		if(preg_match('/^((?<scheme>https?):\/\/(?<hostname>[^\/]+)|)(?<uri>\/.*)/',$path,$matches)){

			if(isset($matches["hostname"])){
				$hostname = $matches["hostname"];
				$server_port = $matches["scheme"]=="https" ? 443 : 80;
				if(preg_match('/^(.+):(\d+)$/',$hostname,$_m)){
					$hostname = $_m[1];
					$server_port = (int)$_m[2];
				}
				$request->setHttpHost($hostname);
				$request->setServerPort($server_port);
				$request->setSslActive($matches["scheme"]=="https");
			}

			$uri = $matches["uri"];
			$route_ar = Atk14Url::RecognizeRoute($uri);
			if($get_params){
				$uri .= preg_match('/\?/',$uri) ? '&' : '?';
				$uri .= http_build_query($get_params);
			}
			$get_params = $route_ar["get_params"] + $get_params;
		}else{

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

			$uri = Atk14Url::BuildLink($get_params + array(
				"namespace" => $namespace,
				"action" => $action,
				"controller" => $controller,
				"lang" => $lang
			),array("connector" => "&"));
		}

		$request->setRequestUri($uri);
		$request->setMethod($method);
		$request->setPostVars($post_params);
		$request->setGetVars($get_params);

		$ctrl = Atk14Dispatcher::Dispatch(array(
			"display_response" => false,
			"request" => $request,
			"return_controller" => true
		));

		$this->controller = $ctrl;

		foreach($GLOBALS["HTTP_RESPONSE"]->getCookies() as $cookie){ $this->addCookie($cookie); }

		$this->_RecentRequest = $request;

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
	 *
	 * <code>
	 *	echo $client->getContentType(); // "UTF-8"
	 * </code>
	 * 
	 * @return string
	 */
	function getContentCharset(){
		return $this->controller->response->getContentCharset();
	}

	/**
	 * Returns length of the content
	 *
	 * @return int
	 */
	function getContentLength(){
		return $this->controller->response->getContentLength();
	}

	/**
	 * Returns response headers
	 *
	 * The header Content-Type is included.
	 *
	 * <code>
	 *	print_r($client->getResponseHeaders());
	 *	Array
	 *	( 
	 *	    [Content-Type] => text/html; charset=UTF-8
	 *	    [Cache-Control] => private, max-age=0, must-revalidate
	 *	)
	 * </code>
	 *
	 * @return array
	 */
	function getResponseHeaders($options = array()){
		$options += array(
			"lowerize_keys" => false,
		);

		$response = $this->controller->response;
		$content_type = $response->getContentType();
		$charset = $response->getContentCharset();
		if($charset){
			$content_type .= "; charset=$charset";
		}
		$headers = array("Content-Type" => $content_type);
		if($response->redirected()){
			$headers["Location"] = $response->getLocation();
		}
		foreach($response->getHeaders() as $key => $value){
			$headers[$key] = $value;
		}

		if($options["lowerize_keys"]){
			$_headers = array();
			foreach($headers as $key => $value){
				$_headers[strtolower($key)] = $value;
			}
			$headers = $_headers;
		}

		return $headers;
	}

	/**
	 * Returns content of the specific response header
	 *
	 * Returns null when the given header doesn't exist
	 *
	 * <code>
	 *	$client->getResponseHeader("Content-Type");
	 *	// or
	 *	$client->getResponseHeader("content-type");
	 * </code>
	 */
	function getResponseHeader($header){
		$header = strtolower($header);
		$ary = $this->getResponseHeaders(array("lowerize_keys" => true));
		return isset($ary[$header]) ? $ary[$header] : null;
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

	/**
	 * Returns the most recent request
	 *
	 * @request HTTPRequest
	 */
	function getRecentRequest(){
		return $this->_RecentRequest;
	}
}

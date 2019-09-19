<?php
class tc_http_request extends tc_base{

	function test_addresses(){
		global $_SERVER;

		$_SERVER["REMOTE_ADDR"] = "1.2.3.4";
		$_SERVER["SERVER_ADDR"] = "10.10.20.20";
		
		$req = new HTTPRequest();

		$this->assertEquals("1.2.3.4",$req->getRemoteAddr());
		$req->setRemoteAddr("5.6.7.8");
		$this->assertEquals("5.6.7.8",$req->getRemoteAddr());

		$this->assertEquals("10.10.20.20",$req->getServerAddr());
		$req->setServerAddr("30.30.40.40");
		$this->assertEquals("30.30.40.40",$req->getServerAddr());
	}

	function test_get_vars(){
		global $_GET, $_POST, $_COOKIE;

		$_GET = array(
			"clanek_id" => 123,
			"sklonuj" => "on",
			"offset" => "10",
		);
 
		$_POST = array(
			"sklonuj" => "off",
			"lang" => "cs"
		);

		$_COOKIE = array(
			"lang" => "en"
		);

		$req = new HTTPRequest();

		$this->_compare_arrays(array(
			"clanek_id" => 123,
			"sklonuj" => "on",
			"offset" => "10",
			"lang" => "cs",
		),$req->getVars());
		$this->assertEquals(123,$req->getGetVar("clanek_id"));
		$this->assertNull($req->getGetVar("lang"));
		$this->assertEquals("cs",$req->getPostVar("lang"));
		$this->assertEquals("on",$req->getGetVar("sklonuj"));
		$this->assertEquals("off",$req->getPostVar("sklonuj"));

		$this->_compare_arrays(array(
			"clanek_id" => 123,
			"sklonuj" => "on",
			"offset" => "10",
		),$req->getVars("G"));

		$this->_compare_arrays(array(
			"sklonuj" => "off",
			"lang" => "cs"
		),$req->getVars("P"));

		$this->_compare_arrays(array(
			"lang" => "en"
		),$req->getVars("C"));

		$this->_compare_arrays(array(
			"lang" => "en",
			"sklonuj" => "off",
		),$req->getVars("CP"));

		$this->_compare_arrays(array(),$req->getVars("??")); // nesmyslny parametr
	}

	function test_get_content_type(){
		global $_SERVER;

		$_SERVER["CONTENT_TYPE"] = "text/xml";

		$req = new HTTPRequest();
		$this->assertEquals("text/xml",$req->getContentType());

		$req->setContentType("text/json");
		$this->assertEquals("text/json",$req->getContentType());
	}

	function test_get_uploaded_file(){
		$this->_init_FILES();

		$req = new HTTPRequest();

		$file = $req->getUploadedFile("dousi");
		$this->assertNull($file);

		$file = $req->getUploadedFile("dousi",array("testing_mode" => true));
		$this->assertTrue(is_object($file));
		$this->assertEquals("dousi",$file->getName());
		$this->assertEquals("Dousi.pdf",$file->getFileName());
		$this->assertEquals("application/pdf",$file->getMimeType());
		$this->assertEquals(15257,$file->getFileSize());
		$this->assertTrue($file->isPdf());
		$this->assertFalse($file->isImage());
		$this->assertNull($file->getImageWidth());
		$this->assertNull($file->getImageHeight());

		// pokud nazadame jmeno, bude vracen prvni soubor v poradi - zde to bude hlava
		$file = $req->getUploadedFile(null,array("testing_mode" => true));
		$this->assertEquals("hlava",$file->getName());
		$this->assertEquals("Hlava.jpg",$file->getFileName());
		$this->assertEquals("image/jpeg",$file->getMimeType());
		$this->assertEquals(21727,$file->getFileSize());
		$this->assertFalse($file->isPdf());
		$this->assertTrue($file->isImage());
		$this->assertEquals(325,$file->getImageWidth());
		$this->assertEquals(448,$file->getImageHeight());
	}

	function test_getUploadedFileError(){
		$this->_init_FILES();

		$req = new HTTPRequest();

		$this->assertEquals(0,$req->getUploadedFileError("hlava"));
		$this->assertEquals(0,$req->getUploadedFileError("dousi"));
		$this->assertEquals(null,$req->getUploadedFileError("strange_name"));

		$GLOBALS["_FILES"]["hlava"]["error"] = 1;

		$this->assertEquals(1,$req->getUploadedFileError("hlava"));
		$this->assertEquals(0,$req->getUploadedFileError("dousi"));
		$this->assertEquals(null,$req->getUploadedFileError("strange_name"));
	}

	function test_get_reuqest_method(){
		$GLOBALS["_SERVER"]["REQUEST_METHOD"] = "GET";
		$GLOBALS["_POST"] = array();
		$GLOBALS["_GET"] = array();
		$GLOBALS["_COOKIE"] = array();
		$m = &$GLOBALS["_SERVER"]["REQUEST_METHOD"];
		$post = &$GLOBALS["_POST"];
		$get = &$GLOBALS["_GET"];
		$cookie = &$GLOBALS["_COOKIE"];

		$req = new HTTPRequest();

		$m = "GET";
		$this->_check_request_method($req,"GET");

		$m = "POST";
		$this->_check_request_method($req,"POST");

		$m = "DELETE";
		$this->_check_request_method($req,"DELETE");

		$m = "PUT";
		$this->_check_request_method($req,"PUT");

		$post["_method"] = "DELETE";
		$m = "POST";
		$this->_check_request_method($req,"DELETE");

		$post["_method"] = "delete";
		$m = "POST";
		$this->_check_request_method($req,"DELETE");

		$post["_method"] = "delete";
		$m = "GET";
		$this->_check_request_method($req,"GET");

		$post = array();

		$get["_method"] = "DELETE";
		$m = "POST";
		$this->_check_request_method($req,"DELETE");

		$get["_method"] = "delete";
		$m = "POST";
		$this->_check_request_method($req,"DELETE");

		$get["_method"] = "delete";
		$m = "GET";
		$this->_check_request_method($req,"GET");

		$get = array();
		
		$cookie["_method"] = "DELETE"; 
		$m = "POST";
		$this->_check_request_method($req,"POST");

		// designate _method in POST vars is more relevant than in GET vars
		$post["_method"] = "DELETE";
		$get["_method"] = "PUT";
		$this->assertEquals("DELETE",$req->getPostVar("_method"));
		$this->assertEquals("PUT",$req->getGetVar("_method"));
		$m = "POST";
		$this->_check_request_method($req,"DELETE");

		// 
		$_POST = array(
			"first_compo" => "atari",
			"second_compo" => "amiga"
		);
		$req = new HTTPRequest();
		$this->assertEquals("atari",$req->getPostVar("first_compo"));
		$this->assertEquals("amiga",$req->getPostVar("second_compo"));

		$req->setPostVars(array(
			"first_compo" => "didaktitk"
		));
		$this->assertEquals("didaktitk",$req->getPostVar("first_compo"));
		$this->assertNull($req->getPostVar("second_compo"));

		$req->setPostVar("second_compo","atari_tt");
		$this->assertEquals("didaktitk",$req->getPostVar("first_compo"));
		$this->assertEquals("atari_tt",$req->getPostVar("second_compo"));
	}

	function test_set_request_method(){
		$GLOBALS["_SERVER"]["REQUEST_METHOD"] = "GET";
		
		$req = new HTTPRequest();

		$this->assertEquals("GET",$req->getMethod());

		$req->setMethod("POST");
		$this->assertEquals("POST",$req->getMethod());

		$req->setMethod("put");
		$this->assertEquals("PUT",$req->getMethod());
	}

	function test_http_host(){
		global $_SERVER;

		$_SERVER["HTTP_HOST"] = "www.test.cz:81";

		$req = new HTTPRequest();

		$this->assertEquals("www.test.cz",$req->getHttpHost());
		
		$req->setHttpHost("www.fake.cz");

		$this->assertEquals("www.fake.cz",$req->getHttpHost());
	}

	function test_getScheme(){
		global $_SERVER;

		unset($_SERVER["HTTPS"]);
		$req = new HTTPRequest();
		$this->assertEquals("http",$req->getScheme());

		$_SERVER["HTTPS"] = "on";
		$req = new HTTPRequest();
		$this->assertEquals("https",$req->getScheme());
	}

	function test_http_referer(){
		global $_SERVER;

		$_SERVER["HTTP_REFERER"] = "https://duckduckgo.com/?q=atari";

		$req = new HTTPRequest();

		$this->assertEquals("https://duckduckgo.com/?q=atari",$req->getHttpReferer());
		
		$req->setHttpReferer("http://www.fake.cz/index.php");

		$this->assertEquals("http://www.fake.cz/index.php",$req->getHttpReferer());
	}

	function test_request_uri(){
		global $_SERVER;

		unset($_SERVER["REQUEST_URI"]);

		$req = new HTTPRequest();

		$this->assertEquals("",$req->getUri());

		$_SERVER["REQUEST_URI"] = "/test-uri/";
		$this->assertEquals("/test-uri/",$req->getUri());

		$req->setUri("/force-uri/");
		$this->assertEquals("/force-uri/",$req->getUri());
		
	}

	function test_set_xhr(){
		$req = new HTTPRequest();
		$this->assertFalse($req->xhr());

		$req->setXhr();
		$this->assertTrue($req->xhr());

		$req->setXhr(false);
		$this->assertFalse($req->xhr());
	}

	function test_get_header(){
		global $_SERVER;

		$req = new HTTPRequest();
		$this->assertTrue(is_array($req->getHeaders()));
		$this->assertEquals(null,$req->getHeader("Non-Existing-Header"));

		$req->_HTTPRequest_headers = array("Test-Header" => "Header_Value");

		$this->assertEquals("Header_Value",$req->getHeader("Test-Header"));
		$this->assertEquals("Header_Value",$req->getHeader("TEST-HEADER"));
		$this->assertEquals("Header_Value",$req->getHeader("test-header"));

		// zjistovani xhr() se hlavicek tyka...
		$this->assertEquals(false,$req->xhr());

		$req->_HTTPRequest_headers = array("X-Requested-With" => "xmlhttprequest");
		$this->assertEquals(true,$req->xhr());

		$req->_HTTPRequest_headers = array("X-Requested-With" => "XmlHttpRequest");
		$this->assertEquals(true,$req->xhr());

		$req->_HTTPRequest_headers = array("x-requested-with" => "XmlHttpRequest");
		$this->assertEquals(true,$req->xhr());

		$req->_HTTPRequest_headers = array("x-requXXestedwith" => "XmlHttpRequest");
		$this->assertEquals(false,$req->xhr());

		// 
		$this->assertEquals(false,$req->xhr());
		$_SERVER["X_ORIGINAL_REQUEST_URI"] = "/?__xhr_request=1";
		$this->assertEquals(true,$req->xhr());
		$_SERVER["X_ORIGINAL_REQUEST_URI"] = "/?id=12&__xhr_request=1";
		$this->assertEquals(true,$req->xhr());
		$_SERVER["X_ORIGINAL_REQUEST_URI"] = "/?__xhr_request=1&id=12";
		$this->assertEquals(true,$req->xhr());
		$_SERVER["X_ORIGINAL_REQUEST_URI"] = "/?id=12&__xhr_request=1&format=xml";
		$this->assertEquals(true,$req->xhr());
		$_SERVER["X_ORIGINAL_REQUEST_URI"] = "/?___xhr_request=1&id=12";
		$this->assertEquals(false,$req->xhr());


		// test setHeader()
		$this->assertEquals(null,$req->getHeader("X-User-Id"));
		$req->setHeader("x-user-id","123");
		$this->assertEquals("123",$req->getHeader("X-User-Id"));
	}

	function test_content_type(){
		$uf = new UrlFetcher("https://jarek.plovarna.cz/atk14/src/http/test/dump_request.php");
		$uf->post("testing data",array(
			"content_type" => "text/plain; charset=UTF-8"
		));

		$content = $uf->getContent();

		$this->assertContains("content-type: text/plain",$content);
		$this->assertContains("content-charset: UTF-8",$content);
	}

	function test_get_user_agent(){
		global $_SERVER;

		$request = new HTTPRequest();

		unset($_SERVER["HTTP_USER_AGENT"]);
		$this->assertEquals(null,$request->getUserAgent());

		$_SERVER["HTTP_USER_AGENT"] = "Real_User_Agent"; 
		$this->assertEquals("Real_User_Agent",$request->getUserAgent());


		$request->setUserAgent("Forced_User_Agent");

		$this->assertEquals("Forced_User_Agent",$request->getUserAgent());
	}

	function test_mobile_device(){
		$request = new HTTPRequest();
		foreach(array(
			/* Firefox on Linux */"Mozilla/5.0 (X11; Linux i686; rv:8.0.1) Gecko/20100101 Firefox/8.0.1" => false,
			/* iPhone*/ "Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3" => true,
			/* iPod */ "Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A101a Safari/419.3" => true,
			/* iPad */ "Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) version/4.0.4 Mobile/7B367 Safari/531.21.10" => false,
		) as $user_agent => $is_mobile_device){
			$request->setUserAgent($user_agent);
			$this->assertEquals($is_mobile_device,$request->mobileDevice(),$user_agent);
		}
	}

	function test_iphone(){
		$request = new HTTPRequest();
		foreach(array(
			/* Firefox on Linux */"Mozilla/5.0 (X11; Linux i686; rv:8.0.1) Gecko/20100101 Firefox/8.0.1" => false,
			/* iPhone*/ "Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3" => true,
			/* iPod */ "Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A101a Safari/419.3" => true,
			/* iPad */ "Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) version/4.0.4 Mobile/7B367 Safari/531.21.10" => false,
		) as $user_agent => $is_iphone){
			$request->setUserAgent($user_agent);
			$this->assertEquals($is_iphone,$request->iphone(),$user_agent);
		}
	}

	function test_raw_post_data(){
		global $HTTP_RAW_POST_DATA;

		$HTTP_RAW_POST_DATA = null;

		$request = new HTTPRequest();
		$this->assertEquals(null,$request->getRawPostData());

		$HTTP_RAW_POST_DATA = "Real_Raw_Content";
		$this->assertEquals("Real_Raw_Content",$request->getRawPostData());

		$request->setRawPostData("Forced_Raw_Content");
		$this->assertEquals("Forced_Raw_Content",$request->getRawPostData());
	}

	function test_basic_auth(){
		global $_SERVER;

		$_SERVER["PHP_AUTH_USER"] = "john_doe";
		$_SERVER["PHP_AUTH_PW"] = "magic";

		$request = new HTTPRequest();

		$this->assertEquals("john_doe",$request->getBasicAuthUsername());
		$this->assertEquals("magic",$request->getBasicAuthPassword());
		$this->assertEquals("john_doe:magic",$request->getBasicAuthString());

		$request->setBasicAuthUsername("carla");
		$request->setBasicAuthPassword("letMEin");

		$this->assertEquals("carla",$request->getBasicAuthUsername());
		$this->assertEquals("letMEin",$request->getBasicAuthPassword());
		$this->assertEquals("carla:letMEin",$request->getBasicAuthString());
	}

	function test_sslActive(){
		global $_SERVER;
		$request = new HTTPRequest();
		$request->setServerPort(80);

		unset($_SERVER["HTTPS"]);
		$this->assertEquals(false,$request->sslActive());

		$_SERVER["HTTPS"]="on";
		$this->assertEquals(true,$request->sslActive());

		unset($_SERVER["HTTPS"]);
		$this->assertEquals(false,$request->sslActive());

		$request->setServerPort(443);
		$this->assertEquals(true,$request->sslActive());

		$request->setServerPort(80);
		$this->assertEquals(false,$request->sslActive());
	}

	function test_getServerName(){
		global $_SERVER;
		$_SERVER["SERVER_NAME"] = "example.com";

		$request = new HTTPRequest();
		$this->assertEquals("example.com",$request->getServerName());

		$request->setServerName("just-for-testing.com");
		$this->assertEquals("just-for-testing.com",$request->getServerName());
	}

	function test_getRequestUri(){
		global $_SERVER;
		$_SERVER["REQUEST_URI"] = "/index.aspx";

		$request = new HTTPRequest();
		$this->assertEquals("/index.aspx",$request->getRequestUri());

		$request->setRequestUri("/index.php");
		$this->assertEquals("/index.php",$request->getRequestUri());
	}

	function test_getQueryString(){
		global $_SERVER;

		$_SERVER["REQUEST_URI"] = "/index.php?foo=bar&name=John+Doe";
		$request = new HTTPRequest();
		$this->assertEquals("foo=bar&name=John+Doe",$request->getQueryString());
		$this->assertEquals("?foo=bar&name=John+Doe",$request->getQueryString(true));

		$_SERVER["REQUEST_URI"] = "/index.php?foo=bar&name=John+Doe&motto=Why%20not?";
		$request = new HTTPRequest();
		$this->assertEquals("foo=bar&name=John+Doe&motto=Why%20not?",$request->getQueryString());
		$this->assertEquals("?foo=bar&name=John+Doe&motto=Why%20not?",$request->getQueryString(true));

		$_SERVER["REQUEST_URI"] = "/index.aspx";
		$request = new HTTPRequest();
		$this->assertEquals("",$request->getQueryString());
		$this->assertEquals("",$request->getQueryString(true));
	}

	function test_getServerUrl(){
		global $_SERVER;

		$_SERVER["REQUEST_URI"] = "/contact.php";
		unset($_SERVER["HTTPS"]);
		$_SERVER["SERVER_PORT"] = "80";
		$_SERVER["HTTP_HOST"] = "www.testiq.cz";
		$request = new HTTPRequest();
		//
		$this->assertEquals("http://www.testiq.cz",$request->getServerUrl());

		$_SERVER["SERVER_PORT"] = "81";
		$request = new HTTPRequest();
		//
		$this->assertEquals("http://www.testiq.cz:81",$request->getServerUrl());

		$request->setRequestAddress("https://www.test.cz:444/calendar.php");
		//
		$this->assertEquals("https://www.test.cz:444",$request->getServerUrl());
	}

	function test_getRequestAddress(){
		global $_SERVER;

		$_SERVER["REQUEST_URI"] = "/contact.php";
		unset($_SERVER["HTTPS"]);
		$_SERVER["SERVER_PORT"] = "81";
		$_SERVER["HTTP_HOST"] = "www.testiq.cz";
		$request = new HTTPRequest();

		$this->assertEquals("http://www.testiq.cz:81/contact.php",$request->getRequestAddress());
		$this->assertEquals("http://www.testiq.cz:81/contact.php",$request->getUrl());

		$request->setRequestAddress("https://www.example.com/list.php");
		$this->assertEquals("https://www.example.com/list.php",$request->getRequestAddress());
		$this->assertEquals("https://www.example.com/list.php",$request->getUrl());

		$request->setUrl("https://www.test.cz/calendar.php");
		$this->assertEquals("https://www.test.cz/calendar.php",$request->getRequestAddress());
		$this->assertEquals("https://www.test.cz/calendar.php",$request->getUrl());

		$_SERVER["HTTPS"] = "on";
		$_SERVER["SERVER_PORT"] = "443";
		$request = new HTTPRequest();

		$this->assertEquals("https://www.testiq.cz/contact.php",$request->getRequestAddress());
		$this->assertEquals("https://www.testiq.cz/contact.php",$request->getUrl());

		$_SERVER["SERVER_PORT"] = "444";
		$request = new HTTPRequest();

		$this->assertEquals("https://www.testiq.cz:444/contact.php",$request->getRequestAddress());
		$this->assertEquals("https://www.testiq.cz:444/contact.php",$request->getUrl());

		$_SERVER["SERVER_PORT"] = "80";
		$request = new HTTPRequest();

		$this->assertEquals("https://www.testiq.cz/contact.php",$request->getRequestAddress());
		$this->assertEquals("https://www.testiq.cz/contact.php",$request->getUrl());
	}

	function test_getGetVars(){
		global $_GET;
		$_GET = array("id" => "123", "format" => "xml");

		$request = new HTTPRequest();
		$this->assertEquals(array("id" => "123", "format" => "xml"),$request->getGetVars());

		$request->setGetVars(array("fake" => "1"));
		$this->assertNull($request->getGetVar("id"));
		$this->assertEquals(array("fake" => "1"),$request->getGetVars());
	}

	function test_getPostVars(){
		global $_POST;
		$_POST = array("id" => "123", "format" => "xml");

		$request = new HTTPRequest();
		$this->assertEquals(array("id" => "123", "format" => "xml"),$request->getPostVars());

		$request->setPostVars(array("fake" => "1"));
		$this->assertEquals(array("fake" => "1"),$request->getPostVars());
	}

	function _check_request_method($req,$type){
		$this->assertEquals($type,$req->getRequestMethod());

		$this->assertEquals($type == "GET",$req->get());
		$this->assertEquals($type == "POST",$req->post());
		$this->assertEquals($type == "DELETE",$req->delete());
		$this->assertEquals($type == "PUT",$req->put());
	}

	function test_getCookieVar(){
		global $_COOKIE;
		$_COOKIE = array("session" => "123456abcd", "lang" => "en");

		$request = new HTTPRequest();
		$this->assertEquals(array("session" => "123456abcd", "lang" => "en"),$request->getCookieVars());
		$this->assertEquals("en",$request->getCookie("lang"));

		$request->setCookieVar("lang","fi");
		$request->setCookieVar("check","1");
		$this->assertEquals("fi",$request->getCookie("lang"));
		$this->assertEquals(array("session" => "123456abcd", "lang" => "fi","check" => "1"),$request->getCookieVars());
	}

	function test_server_port(){
		global $_SERVER;

		unset($_SERVER["HTTPS"]);

		$_SERVER["SERVER_PORT"] = 80;
		$request = new HTTPRequest();

		$this->assertEquals(80,$request->getServerPort());
		$this->assertEquals(false,$request->ssl());
		$this->assertEquals(true,$request->isServerOnStandardPort());

		$_SERVER["SERVER_PORT"] = 81;
		$request = new HTTPRequest();

		$this->assertEquals(81,$request->getServerPort());
		$this->assertEquals(false,$request->ssl());
		$this->assertEquals(false,$request->isServerOnStandardPort());

		$_SERVER["HTTPS"] = "on";

		$_SERVER["SERVER_PORT"] = 443;
		$request = new HTTPRequest();

		$this->assertEquals(443,$request->getServerPort());
		$this->assertEquals(true,$request->ssl());
		$this->assertEquals(true,$request->isServerOnStandardPort());

		$_SERVER["SERVER_PORT"] = 444;
		$request = new HTTPRequest();

		$this->assertEquals(444,$request->getServerPort());
		$this->assertEquals(true,$request->ssl());
		$this->assertEquals(false,$request->isServerOnStandardPort()); // Yes, it's ok! It's quite common that Apache is running on non-ssl port 80 and ssl is provided by Nginx in reverse proxy mode.

		$_SERVER["SERVER_PORT"] = 80;
		$request = new HTTPRequest();

		$this->assertEquals(80,$request->getServerPort());
		$this->assertEquals(true,$request->ssl());
		$this->assertEquals(true,$request->isServerOnStandardPort());
	}

	function test_getRemoteHostname(){
		$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		$request = new HTTPRequest();
		$this->assertTrue(in_array($request->getRemoteHostname(),array("localhost.localdomain","localhost")));

		$_SERVER["REMOTE_ADDR"] = "8.8.8.8";
		$request = new HTTPRequest();
		$this->assertEquals("dns.google",$request->getRemoteHostname());

		unset($_SERVER["REMOTE_ADDR"]);
		$request = new HTTPRequest();
		$this->assertEquals(null,$request->getRemoteHostname());
	}

	/**
	* Porovna dve asociativni pole bez ohledu na poradi klicu.
	*/
	function _compare_arrays($template,$arry){
		$this->assertEquals(sizeof($template),sizeof($arry));

		$arry_keys = array_keys($arry);

		foreach($template as $_key => $_val){
			$this->assertTrue(in_array($_key,$arry_keys));
			$this->assertEquals($_val,$arry[$_key]);
		}
	}

}

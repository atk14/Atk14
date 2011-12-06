<?
class tc_http_request extends tc_base{
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
	}

	function test_content_type(){
		$uf = new UrlFetcher("http://127.0.0.1/sources/http/test/dump_request.php");
		$uf->post("testing data",array(
			"content_type" => "text/plain; charset=UTF-8"
		));

		$ar = miniYAML::Load($uf->getContent());
		$this->assertEquals("text/plain",$ar["content-type"]);
		$this->assertEquals("UTF-8",$ar["content-charset"]);
	}

	function test_get_user_agent(){
		global $_SERVER;
		$_SERVER["HTTP_USER_AGENT"] = "Real_User_Agent"; 

		$request = new HTTPRequest();

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

	function _check_request_method($req,$type){
		$this->assertEquals($type,$req->getRequestMethod());

		$this->assertEquals($type == "GET",$req->get());
		$this->assertEquals($type == "POST",$req->post());
		$this->assertEquals($type == "DELETE",$req->delete());
		$this->assertEquals($type == "PUT",$req->put());
	}

	/**
	* Porovna dve asociativni pole bez ohledu na poradi klicu.
	*/
	function _compare_arrays($template,$arry){
		$this->assertEquals(sizeof($template),sizeof($arry));

		$arry_keys = array_keys($arry);

		reset($template);
		while(list($_key,$_val) = each($template)){
			$this->assertTrue(in_array($_key,$arry_keys));
			$this->assertEquals($_val,$arry[$_key]);
		}
	}

}

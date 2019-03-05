<?php
class tc_url_fetcher extends tc_base{

	function test(){
		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/dungeon-master.png");
		$this->assertTrue($f->fetchContent());
		$this->assertFalse($f->errorOccurred());
		$this->assertTrue($f->found());
		$this->assertEquals("https://jarek.plovarna.cz/unit-testing/dungeon-master.png",$f->getUrl());
		$this->assertEquals("GET",$f->getRequestMethod());
		$this->assertEquals(11462,strlen($f->getContent()));
		$this->assertEquals("c4f99bdb6a4feb3b41b1bcd56a4d7aa3",md5($f->getContent()));
		$this->assertEquals("image/png",$f->getContentType());
		$this->assertEquals("image/png",$f->getHeaderValue("Content-Type"));
		$this->assertEquals("image/png",$f->getHeaderValue("content-type"));
		$this->assertEquals("image/png",$f->getHeaderValue("content-type"));
		$this->assertEquals("image/png",$f->getHeader("content-type"));
		$this->assertEquals(null,$f->getHeaderValue("content-type-xxx"));

		$this->assertEquals(200,$f->getStatusCode());
		$this->assertEquals("OK",$f->getStatusMessage());
		$this->assertEquals("dungeon-master.png",$f->getFilename());

		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/dungeon-master.png?not_important_parameter=1");
		$this->assertTrue($f->found());
		$this->assertEquals("dungeon-master.png",$f->getFilename());

		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/content_disposition.php");
		$this->assertTrue($f->found());
		$this->assertEquals("sample.dat",$f->getFilename());

		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/non-existing");
		//$this->assertFalse($f->fetchContent()); // nemusi byt volano!
		$this->assertFalse($f->found());
		$this->assertTrue($f->errorOccurred());
		$this->assertEquals(404,$f->getStatusCode()); // Not Found
		$this->assertEquals("Not Found",$f->getStatusMessage());
		$this->assertContains('<title>404 Not Found</title>',$f->getContent());

		// notice: connecting to port 8124 causes warning messages appearance 
		$f = new UrlFetcher("http://127.0.0.1:8124/pretty-stupid-port");
		@$this->assertFalse($f->found());
		$this->assertEquals(null,$f->getStatusCode());
		$this->assertEquals(null,$f->getContent());

		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/zeroes.dat");
		$this->assertEquals(4,strlen($f->getContent()));
		$this->assertEquals(chr(0).chr(0).chr(0).chr(0),$f->getContent());

		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/zero_data_zero.dat");
		$this->assertEquals(6,strlen($f->getContent()));
		$this->assertEquals(chr(0)."data".chr(0),$f->getContent());

		// Default User-Agent
		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/dungeon-master.png");
		$this->assertTrue($f->found());
		$headers = $f->getRequestHeaders();
		$this->assertContains("User-Agent: UrlFetcher",$headers);
		$this->assertNotContains("User-Agent: curl",$headers);
		
		// Custom User-Agent
		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/dungeon-master.png",array("user_agent" => "curl/7.35.0"));
		$this->assertTrue($f->found());
		$headers = $f->getRequestHeaders();
		$this->assertNotContains("User-Agent: UrlFetcher",$headers);
		$this->assertContains("User-Agent: curl",$headers);
	}

	function test_authorization(){
		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/private/");
		//$this->assertFalse($f->fetchContent());
		$this->assertFalse($f->found());
		$this->assertTrue($f->errorOccurred());
		$this->assertEquals(401,$f->getStatusCode()); // Authorization Required

		$f = new UrlFetcher("http://test:wrong_password@jarek.plovarna.cz/unit-testing/private/");
		//$this->assertFalse($f->fetchContent());
		$this->assertFalse($f->found());
		$this->assertTrue($f->errorOccurred());
		$this->assertEquals(401,$f->getStatusCode()); // Authorization Required

		$f = new UrlFetcher("http://test:UNIT@jarek.plovarna.cz/unit-testing/private/");
		//$this->assertTrue($f->fetchContent());
		$this->assertTrue($f->found());
		$this->assertTrue((bool)preg_match("/Welcome in private area!/",$f->getContent()));

		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/private/");
		$f->setAuthorization("test","UNIT");
		//$this->assertTrue($f->fetchContent());
		$this->assertTrue($f->found());
		$this->assertTrue((bool)preg_match("/Welcome in private area!/",$f->getContent()));
	}

	function test_ssl(){
		$f = new UrlFetcher("https://admin.plovarna.cz/unit-testing.php");
		$this->assertTrue($f->found());
		$this->assertTrue((bool)preg_match("/You are on the ssl/",$f->getContent()));
		$this->assertTrue((bool)preg_match("/Request method is GET/",$f->getContent()));
	}

	function test_post(){
		$this->_test_post("foo=bar&foo_2=bar_too");
		$this->_test_post(array(
			"foo" => "bar",
			"foo_2" => "bar_too"
		));
	}

	function test_put(){
		$f = new UrlFetcher();
		$f->put("http://www.atk14.net/api/en/http_requests/detail/?format=json");
		$this->assertEquals(200,$f->getStatusCode());
		$data = json_decode($f->getContent(),true);
		$this->assertEquals("PUT",$data["method"]);
	}

	function test_delete(){
		$f = new UrlFetcher();
		$f->delete("http://www.atk14.net/api/en/http_requests/detail/?format=json");
		$this->assertEquals(200,$f->getStatusCode());
		$data = json_decode($f->getContent(),true);
		$this->assertEquals("DELETE",$data["method"]);
	}

	function test_additiona_headers(){
		$f = new UrlFetcher("https://jarek.plovarna.cz/",array(	
			"additional_headers" => array(
				"X-App-Version: 1.2",
			)
		));
		$this->assertTrue($f->found());
		$this->assertTrue((bool)preg_match("/X-App-Version/",$f->getContent()));
	}

	function _test_post($data){
		$f = new UrlFetcher("https://admin.plovarna.cz/unit-testing.php");
		$this->assertTrue($f->post($data));
		$this->assertTrue((bool)preg_match("/You are on the ssl/",$f->getContent()));
		$this->assertTrue((bool)preg_match("/Request method is POST/",$f->getContent()));
		$this->assertEquals("POST",$f->getRequestMethod());

		$headers = $f->getRequestHeaders();
		$this->assertContains("User-Agent: UrlFetcher",$headers);
		$this->assertNotContains("User-Agent: curl",$headers);
		$this->assertTrue((bool)preg_match("/Content-Length: 21/",$headers));
		$this->assertTrue((bool)preg_match("/Content-Type: application\\/x-www-form-urlencoded/",$headers));
	}

	function test_get_content_charset(){
		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/latin2.php");
		$this->assertTrue($f->found());
		$this->assertEquals("text/html",$f->getContentType());
		$this->assertEquals("iso-8859-2",$f->getContentCharset());

		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/no_charset.php");
		$this->assertTrue($f->found());
		$this->assertEquals("text/html",$f->getContentType());
		$this->assertEquals(null,$f->getContentCharset());
	}

	function test_redirecting(){
		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/redirection.php?type=full_address");
		$this->assertEquals("https://jarek.plovarna.cz/unit-testing/redirection.php?type=full_address",$f->getUrl()); // at the moment UF doesn't know that the given URL will be redirected
		$this->assertEquals(200,$f->getStatusCode());
		$this->assertEquals("TEST CONTENT, type=full_address",trim($f->getContent()));
		$this->assertEquals("https://jarek.plovarna.cz/unit-testing/content.php?type=full_address",$f->getUrl());

		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/redirection.php?type=absolute");
		$this->assertEquals(200,$f->getStatusCode());
		$this->assertEquals("TEST CONTENT, type=absolute",trim($f->getContent()));

		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/redirection.php?type=relative");
		$this->assertEquals(200,$f->getStatusCode());
		$this->assertEquals("TEST CONTENT, type=relative",trim($f->getContent()));

		// -- POST
		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/redirection.php?type=full_address");
		$f->post();
		$this->assertEquals(200,$f->getStatusCode());
		$this->assertEquals("TEST CONTENT, type=full_address",$f->getContent());
		$this->assertEquals("GET",$f->getRequestMethod());

		// -- disable redirection
		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/redirection.php?type=full_address",array("max_redirections" => 0));
		$this->assertEquals(302,$f->getStatusCode());
		$this->assertEquals("",$f->getContent());
		$this->assertEquals("GET",$f->getRequestMethod());

		// -- disable redirection & POST
		$f = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/redirection.php?type=full_address",array("max_redirections" => 0));
		$f->post();
		$this->assertEquals(302,$f->getStatusCode());
		$this->assertEquals("",$f->getContent());
		$this->assertEquals("POST",$f->getRequestMethod());
	}
}

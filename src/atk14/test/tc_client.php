<?php
class TcClient extends TcBase{

	function test(){
		$client = new Atk14Client();

		$controller = $client->get("testing/test",array("id" => "123", "format" => "xml"));
		$this->assertEquals("/cs/testing/test/?id=123&format=xml",$controller->request->getRequestUri());
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("123",$controller->params->g("id"));
		$this->assertEquals("xml",$controller->params->g("format"));
		$this->assertEquals(true,$controller->request->get());
		$this->assertEquals(false,$controller->request->post());
		//
		// getResponseHeaders()
		$this->assertEquals(array(
			"Content-Type" => "text/html; charset=UTF-8",
			"X-Test-Header" => "Yep"
		),$client->getResponseHeaders());
		$this->assertEquals(array(
			"content-type" => "text/html; charset=UTF-8",
			"x-test-header" => "Yep"
		),$client->getResponseHeaders(array("lowerize_keys" => true)));
		//
		// getResponseHeader()
		$this->assertEquals("text/html; charset=UTF-8",$client->getResponseHeader("Content-Type"));
		$this->assertEquals("text/html; charset=UTF-8",$client->getResponseHeader("content-type"));
		$this->assertEquals("Yep",$client->getResponseHeader("X-TEST-HEADER"));
		$this->assertEquals(null,$client->getResponseHeader("X-Non-Existing"));

		$controller = $client->post("testing/test",array("id" => "123", "format" => "xml"));
		$this->assertEquals("/cs/testing/test/",$controller->request->getRequestUri());
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("123",$controller->params->g("id"));
		$this->assertEquals("xml",$controller->params->g("format"));
		$this->assertEquals(false,$controller->request->get());
		$this->assertEquals(true,$controller->request->post());

		$controller = $client->post("testing/test","<xml></xml>",array("content_type" => "text/xml"));
		$this->assertEquals("/cs/testing/test/",$controller->request->getRequestUri());
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals(true,$controller->params->isEmpty());
		$this->assertEquals(false,$controller->request->get());
		$this->assertEquals(true,$controller->request->post());
		$this->assertEquals("text/xml",$controller->request->getContentType());
		$this->assertEquals("<xml></xml>",$controller->request->getRawPostData());

		// Paths starting with "/"
		$controller = $client->get("/cs/testing/test/?id=333&format=yaml");
		$this->assertEquals("/cs/testing/test/?id=333&format=yaml",$controller->request->getRequestUri());
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("333",$controller->params->g("id"));
		$this->assertEquals("yaml",$controller->params->g("format"));
		$this->assertEquals(true,$controller->request->get());
		$this->assertEquals(false,$controller->request->post());

		$controller = $client->get("/en/testing/test/?id=111&format=yaml",array("firstname" => "Samantha"));
		$this->assertEquals("/en/testing/test/?id=111&format=yaml&firstname=Samantha",$controller->request->getRequestUri());
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("111",$controller->params->g("id"));
		$this->assertEquals("yaml",$controller->params->g("format"));
		$this->assertEquals("Samantha",$controller->params->g("firstname"));
		$this->assertEquals(true,$controller->request->get());
		$this->assertEquals(false,$controller->request->post());

		$controller = $client->get("/sk/testing/test/",array("firstname" => "Samantha", "lastname" => "Doe"));
		$this->assertEquals("/sk/testing/test/?firstname=Samantha&lastname=Doe",$controller->request->getRequestUri());
		$this->assertEquals(200,$client->getStatusCode());

		// Complete URLs
		$controller = $client->get("https://example.com/cs/testing/test/?id=555&format=xml");
		$this->assertEquals("/cs/testing/test/?id=555&format=xml",$controller->request->getRequestUri());
		$this->assertEquals("example.com",$controller->request->getHttpHost());
		$this->assertEquals(443,$controller->request->getServerPort());
		$this->assertEquals(true,$controller->request->ssl());
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("555",$controller->params->g("id"));
		$this->assertEquals("xml",$controller->params->g("format"));
		$this->assertEquals(true,$controller->request->get());
		$this->assertEquals(false,$controller->request->post());

		$controller = $client->get("http://www.atk14.net/");
		$this->assertEquals("/",$controller->request->getRequestUri());
		$this->assertEquals("www.atk14.net",$controller->request->getHttpHost());
		$this->assertEquals(80,$controller->request->getServerPort());
		$this->assertEquals(false,$controller->request->ssl());

		$controller = $client->get("https://www.atk14.net:444/sitemap.xml");
		$this->assertEquals("/sitemap.xml",$controller->request->getRequestUri());
		$this->assertEquals("www.atk14.net",$controller->request->getHttpHost());
		$this->assertEquals(444,$controller->request->getServerPort());
		$this->assertEquals(true,$controller->request->ssl());

		// here, the parameter id is doubled
		$controller = $client->get("https://example.com/cs/testing/test/?id=333&format=xml",array("p1" => "1","id" => "444"));
		$this->assertEquals("/cs/testing/test/?id=333&format=xml&p1=1&id=444",$controller->request->getRequestUri());
		$this->assertEquals("333",$controller->params->g("id"));
		$this->assertEquals("xml",$controller->params->g("format"));
		$this->assertEquals("1",$controller->params->g("p1"));
		$this->assertEquals(true,$controller->request->get());
		$this->assertEquals(false,$controller->request->post());

		// Missing ending slash
		$controller = $client->get("/sk/testing/test",array("firstname" => "James", "lastname" => "Doe"));
		$this->assertEquals("/sk/testing/test/?firstname=James&lastname=Doe",$client->getLocation());
		$this->assertEquals(301,$client->getStatusCode());
		$this->assertEquals(array(
			'Content-Type' => 'text/html; charset=UTF-8',
			'Location' => '/sk/testing/test/?firstname=James&lastname=Doe',
			'X-Test-Header' => 'Yep'
		),$client->getResponseHeaders());

		// 404
		$controller = $client->get("/sk/testing/nonexisting/",array("firstname" => "Samuel"));
		$this->assertEquals("/sk/testing/nonexisting/?firstname=Samuel",$controller->request->getRequestUri());
		$this->assertEquals(404,$client->getStatusCode());
		//
		$controller = $client->post("/sk/testing/nonexisting/",array("firstname" => "Samuel"));
		$this->assertEquals("/sk/testing/nonexisting/",$controller->request->getRequestUri());
		$this->assertEquals(404,$client->getStatusCode());

		// POST
		$controller = $client->post("/en/testing/test/?id=444&format=json",array("firstname" => "John"));
		$this->assertEquals("/en/testing/test/?id=444&format=json",$controller->request->getRequestUri());
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("444",$controller->params->g("id"));
		$this->assertEquals("John",$controller->params->g("firstname"));
		$this->assertEquals(false,$controller->request->get());
		$this->assertEquals(true,$controller->request->post());

		//$this->client->get("/cs/main/index/?param=val");
		//$this->

		// Basic Auth
		$this->assertEquals(null,$controller->request->getBasicAuthString());
		$this->assertEquals(null,$client->getBasicAuthString());
		$this->assertEquals(null,$client->getBasicAuthUsername());
		$this->assertEquals(null,$client->getBasicAuthPassword());

		$client->setBasicAuth("admin","secret");
		$controller = $client->post("testing/test","<xml></xml>",array("content_type" => "text/xml"));
		$this->assertEquals("admin:secret",$controller->request->getBasicAuthString());
		$this->assertEquals("admin:secret",$client->getBasicAuthString());
		$this->assertEquals("admin",$client->getBasicAuthUsername());
		$this->assertEquals("secret",$client->getBasicAuthPassword());

		$client->setBasicAuth("bob:theUglyOne");
		$controller = $client->post("testing/test","<xml></xml>",array("content_type" => "text/xml"));
		$this->assertEquals("bob:theUglyOne",$controller->request->getBasicAuthString());
		$this->assertEquals("bob:theUglyOne",$client->getBasicAuthString());
		$this->assertEquals("bob",$client->getBasicAuthUsername());
		$this->assertEquals("theUglyOne",$client->getBasicAuthPassword());

		$client->setBasicAuthString("john:aMagic");
		$controller = $client->post("testing/test","<xml></xml>",array("content_type" => "text/xml"));
		$this->assertEquals("john:aMagic",$controller->request->getBasicAuthString());

		$client->setBasicAuthString("");
		$controller = $client->post("testing/test","<xml></xml>",array("content_type" => "text/xml"));
		$this->assertEquals(null,$controller->request->getBasicAuthString());
		$this->assertEquals(null,$client->getBasicAuthString());
		$this->assertEquals(null,$client->getBasicAuthUsername());
		$this->assertEquals(null,$client->getBasicAuthPassword());

		// Cookies
		$controller = $client->get("testing/cookies_dumper");
		$cookies = $this->_parse_cookies($controller);
		$this->assertEquals(1,sizeof($cookies));
		$this->assertTrue(isset($cookies["check"]));

		$client->disableCookies();
		$controller = $client->get("testing/cookies_dumper");
		$cookies = $this->_parse_cookies($controller);
		$this->assertEquals(array(),$cookies);

		$client->enableCookies();
		$client->addCookie(new HTTPCookie("login_id","123"));
		$controller = $client->get("testing/cookies_dumper");
		$cookies = $this->_parse_cookies($controller);
		$this->assertEquals(2,sizeof($cookies));
		$this->assertTrue(isset($cookies["check"]));
		$this->assertEquals("123",$cookies["login_id"]);

		// testing clearing cookies in cookies disabled mode
		$client->disableCookies();
		$this->assertEquals(0,sizeof($client->getCookies()));
		$client->enableCookies();
		$this->assertEquals(2,sizeof($client->getCookies()));
		$client->disableCookies();
		$client->clearCookies();
		$this->assertEquals(0,sizeof($client->getCookies()));
		$client->enableCookies();
		$this->assertEquals(0,sizeof($client->getCookies()));

		// set cookie
		$client->addCookie(new HTTPCookie("language","cs"));
		$cookies = $client->getCookies();
		$this->assertEquals("cs",$cookies["language"]);
		// set cookie with a different value
		$client->addCookie(new HTTPCookie("language","en"));
		$cookies = $client->getCookies();
		$this->assertEquals("en",$cookies["language"]);
		// set deleting cookie
		$client->addCookie(new HTTPCookie("language","",array("expire" => time() - 60 * 60 * 24)));
		$cookies = $client->getCookies();
		$this->assertTrue(!isset($cookies["language"]));

		// setting new cookie
		$client->get("testing/set_cookie");
		$cookies = $client->getCookies();
		$this->assertEquals(true,sizeof($cookies)>0);
		$this->assertEquals("John Doe",$cookies["user_name"]);
	}

	function _parse_cookies($controller){
		// see app/controllers/testing_controller.php, method cookies_dumper
		$src = '$cookies = '.$controller->response->buffer->toString().';';
		eval($src);
		return $cookies;
	}
}

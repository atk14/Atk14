<?php
class TcClient extends TcBase{
	function test(){
		$client = new Atk14Client();

		$controller = $client->get("testing/test",array("id" => "123", "format" => "xml"));
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("123",$controller->params->g("id"));
		$this->assertEquals("xml",$controller->params->g("format"));
		$this->assertEquals(true,$controller->request->get());
		$this->assertEquals(false,$controller->request->post());
		$this->assertEquals(array(
			"X-Test-Header" => "Yep"
		),$client->getResponseHeaders());

		$controller = $client->post("testing/test",array("id" => "123", "format" => "xml"));
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("123",$controller->params->g("id"));
		$this->assertEquals("xml",$controller->params->g("format"));
		$this->assertEquals(false,$controller->request->get());
		$this->assertEquals(true,$controller->request->post());

		$controller = $client->post("testing/test","<xml></xml>",array("content_type" => "text/xml"));
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals(true,$controller->params->isEmpty());
		$this->assertEquals(false,$controller->request->get());
		$this->assertEquals(true,$controller->request->post());
		$this->assertEquals("text/xml",$controller->request->getContentType());
		$this->assertEquals("<xml></xml>",$controller->request->getRawPostData());

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

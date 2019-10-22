<?php
class tc_http_response extends tc_base{
	function test_location(){
		$response = new HTTPResponse();
		$this->assertEquals(200,$response->getStatusCode());

		$response->setLocation("http://www.domenka.cz/");
		$this->assertEquals(302,$response->getStatusCode()); 

		$response->setLocation("http://www.domenka.cz/",array("moved_permanently" => true));
		$this->assertEquals(301,$response->getStatusCode()); 

		$response->setLocation("http://www.domenka.cz/",array("status" => 301));
		$this->assertEquals(301,$response->getStatusCode()); 

		$response->setLocation("http://www.domenka.cz/",array("status" => 303));
		$this->assertEquals(303,$response->getStatusCode()); 

		$response->setLocation(null); // vynulovani presmerovani
		$this->assertEquals(200,$response->getStatusCode()); 
	}

	function test_forbidden(){
		$response = new HTTPResponse();
		$response->forbidden();
		$this->assertEquals(403,$response->getStatusCode());
		$output = $response->buffer->toString();
		$output = str_replace("\n"," ",$output);
		$this->assertTrue((bool)preg_match("/<h1>Forbidden<\\/h1>/",$output));
		$this->assertTrue((bool)preg_match("/You don't have permission to access/",$output));

		$response = new HTTPResponse();
		$response->forbidden("Insufficient privileges.");
		$output = $response->buffer->toString();
		$this->assertFalse((bool)preg_match("/You don't have permission to access/",$output));
		$this->assertTrue((bool)preg_match("/Insufficient privileges/",$output));
	}

	function test_not_found(){
		$response = new HTTPResponse();
		$response->notFound();
		$this->assertEquals(404,$response->getStatusCode());
		$output = $response->buffer->toString();
		$this->assertTrue((bool)preg_match("/<h1>Not Found<\\/h1>/",$output));
		$this->assertTrue((bool)preg_match("/The requested URL .* was not found on this server/",$output));

		$response = new HTTPResponse();
		$response->notFound("There is no such file.");
		$output = $response->buffer->toString();
		$this->assertFalse((bool)preg_match("/The requested URL .* was not found on this server/",$output));
		$this->assertTrue((bool)preg_match("/There is no such file./",$output));
	}

	function test_internal_server_errors(){
		$response = new HTTPResponse();
		$response->internalServerError();
		$this->assertEquals(500,$response->getStatusCode());
		$output = $response->buffer->toString();
		$this->assertTrue((bool)preg_match("/<h1>Internal Server Error<\\/h1>/",$output));
		$this->assertTrue((bool)preg_match("/<p>Internal server error.<\\/p>/",$output));

		$response = new HTTPResponse();
		$response->internalServerError("An Error occurs.");
		$output = $response->buffer->toString();
		$this->assertFalse((bool)preg_match("/<p>Internal server error.<\\/p>/",$output));
		$this->assertTrue((bool)preg_match("/An Error occurs./",$output));
	}

	function test_redirected(){
		$response = new HTTPResponse();

		$this->assertEquals(200,$response->getStatusCode());
		$this->assertFalse($response->redirected());

		$response->setLocation("/new-uri/");
		$this->assertEquals(302,$response->getStatusCode());
		$this->assertTrue($response->redirected());

		$response->setLocation("/new-uri/",array("moved_permanently" => true));
		$this->assertEquals(301,$response->getStatusCode());
		$this->assertTrue($response->redirected());
	}

	function test_setHeader(){
		$response = new HTTPResponse();

		$this->assertEquals(null,$response->getHeader("X-User-Id"));

		$response->setHeader("x-user-id","123");
		$this->assertEquals("123",$response->getHeader("X-User-Id"));

		$response->setHeader("X-USER-ID","456");
		$this->assertEquals("456",$response->getHeader("X-User-Id"));

		$response->setHeader("X-User-Id: 789");
		$this->assertEquals("789",$response->getHeader("X-USER-ID"));
		$this->assertEquals("789",$response->getHeader("X-User-Id"));
		$this->assertEquals("789",$response->getHeader("x-user-id"));

		$response->setHeaders(array(
			"X-User-Id" => "333",
			"X-Powered-By" => "ATK14 Framework",
		));

		$this->assertEquals("333",$response->getHeader("X-User-Id"));
		$this->assertEquals("ATK14 Framework",$response->getHeader("X-Powered-By"));
	}

	function test_cookies(){
		$day = 60 * 60 * 24;
		$far_future = time() + $day * 365 * 20;
		$resp = new HTTPResponse();

		// setting cookies
		$resp->addCookie(new HTTPCookie("check","1"));
		$resp->addCookie("last_logged_at","2016-02-03",array("expire" => $far_future));
		// using alias
		$resp->setCookie(new HTTPCookie("login","john"));
		$resp->setCookie("last_logged_as","bob",array("expire" => $far_future + $day));

		//
		$cookies = $resp->getCookies();
		$this->assertEquals(4,sizeof($cookies));

		$this->assertEquals("check",$cookies[0]->getName());
		$this->assertEquals("1",$cookies[0]->getValue());

		$this->assertEquals("last_logged_at",$cookies[1]->getName());
		$this->assertEquals("2016-02-03",$cookies[1]->getValue());
		$this->assertEquals($far_future,$cookies[1]->getExpire());

		$this->assertEquals("login",$cookies[2]->getName());
		$this->assertEquals("john",$cookies[2]->getValue());

		$this->assertEquals("last_logged_as",$cookies[3]->getName());
		$this->assertEquals("bob",$cookies[3]->getValue());
		$this->assertEquals($far_future + $day,$cookies[3]->getExpire());

		// clearing cookies
		$this->assertEquals(4,sizeof($resp->getCookies()));
		$resp->clearCookies();
		$this->assertEquals(0,sizeof($resp->getCookies()));
	}

	function test_concatenate(){
		$final_resp = new HTTPResponse();
		$final_resp->setContentType("text/html");
		//
		$final_resp->addCookie(new HTTPCookie("check","1"));
		$final_resp->setHeader("X-Powered-By","PHP");
		//
		$this->assertEquals("text/html",$final_resp->getContentType());
		$this->assertEquals(1,sizeof($final_resp->getCookies()));
		$this->assertEquals(1,sizeof($final_resp->getHeaders()));
		$this->assertEquals(200,$final_resp->getStatusCode());
		$this->assertEquals("OK",$final_resp->getStatusMessage());

		$resp = new HTTPResponse();
		$resp->setContentType("text/plain");
		$resp->setStatusCode("299 You Found a Treasure");
		$resp->addCookie(new HTTPCookie("secret","daisy"));
		$resp->setHeader("X-Powered-By","ATK14 Framework");
		$resp->setHeader("X-Forwarded-For","1.2.3.4");

		$final_resp->concatenate($resp);

		$this->assertEquals(299,$final_resp->getStatusCode());
		$this->assertEquals("You Found a Treasure",$final_resp->getStatusMessage());
		//
		$this->assertEquals("text/plain",$final_resp->getContentType());
		//
		$cookies = $final_resp->getCookies();
		$this->assertEquals(2,sizeof($cookies));
		$this->assertEquals($cookies[0]->getName(),"check");
		$this->assertEquals($cookies[1]->getName(),"secret");
		//
		$headers = $final_resp->getHeaders();
		$this->assertEquals(2,sizeof($headers));
		$this->assertEquals(array(
			"X-Powered-By" => "ATK14 Framework",
			"X-Forwarded-For" => "1.2.3.4",
		),$headers);

		// _OutputBuffer_Flush_Started - a kind of testing :)
		$final_resp = new HTTPResponse();
		$this->assertEquals(false,$final_resp->_OutputBuffer_Flush_Started);
		$resp = new HTTPResponse();
		$resp->write(_("Hello World!"));
		$final_resp->concatenate($resp);
		$this->assertEquals(false,$final_resp->_OutputBuffer_Flush_Started);
		//
		$resp = new HTTPResponse();
		$resp->write(_("Hello Another World!"));
		$resp->_OutputBuffer_Flush_Started = true;
		$final_resp->concatenate($resp);
		$this->assertEquals(true,$final_resp->_OutputBuffer_Flush_Started);
	}

	// TODO: this test is stupid and fails -> rewrite it
	function _test_set_location(){
		$resp = new HTTPResponse();
		$resp->setLocation("/new-uri/");
		$f = $this->_fetch_response($resp);
		$this->assertEquals(302,$f->getStatusCode());
		$this->assertEquals("/new-uri/",$f->getHeaderValue("Location"));

		$main_resp = new HTTPResponse();
		$resp = new HTTPResponse();
		$resp->setLocation("/new-uri-concat/");
		$main_resp->concatenate($resp);
		$main_resp->write("concatenated");
		$f = $this->_fetch_response($main_resp);
		$this->assertEquals(302,$f->getStatusCode());
		$this->assertEquals("/new-uri-concat/",$f->getHeaderValue("Location"));
		$this->assertEquals("concatenated",$f->getContent());

		$resp = new HTTPResponse();
		$resp->setLocation("/new-perma-uri/",array("moved_permanently" => true));
		$f = $this->_fetch_response($resp);
		$this->assertEquals(301,$f->getStatusCode());
		$this->assertEquals("/new-perma-uri/",$f->getHeaderValue("Location"));

		$main_resp = new HTTPResponse();
		$resp = new HTTPResponse();
		$resp->setLocation("/new-perma-uri-concat/",array("moved_permanently" => true));
		$main_resp->concatenate($resp);
		$main_resp->write("concatenated");
		$f = $this->_fetch_response($main_resp);
		$this->assertEquals(301,$f->getStatusCode());
		$this->assertEquals("/new-perma-uri-concat/",$f->getHeaderValue("Location"));
		$this->assertEquals("concatenated",$f->getContent());
	}

	function test_status_message(){
		$resp = new HTTPResponse();
		$this->assertEquals("OK",$resp->getStatusMessage());

		$resp->setStatusCode(404);
		$this->assertEquals("Not Found",$resp->getStatusMessage());

		$resp->setStatusCode(499);
		$this->assertEquals("Unknown",$resp->getStatusMessage());

		$resp->setStatusCode(499,"Custom Error Msg");
		$this->assertEquals("Custom Error Msg",$resp->getStatusMessage());

		$resp->setStatusCode(404);
		$this->assertEquals("Not Found",$resp->getStatusMessage());

		// setting code & message in a single parameter

		$resp->setStatusCode("200 We Found It");
		$this->assertEquals(200,$resp->getStatusCode());
		$this->assertEquals("We Found It",$resp->getStatusMessage());
	}

	function test_setContentType(){
		$r = new HTTPResponse();
		$r->setContentCharset("UTF-8");

		$this->assertEquals("text/html",$r->getContentType());
		$this->assertEquals("UTF-8",$r->getContentCharset());

		$r->setContentType("text/plain");
		$this->assertEquals("text/plain",$r->getContentType());
		$this->assertEquals("UTF-8",$r->getContentCharset());

		$r->setContentType("text/xml; charset=ISO-8859-2");
		$this->assertEquals("text/xml",$r->getContentType());
		$this->assertEquals("ISO-8859-2",$r->getContentCharset());

		$r->setHeader("Content-Type: text/html; charset=WINDOWS-1250");
		$this->assertEquals("text/html",$r->getContentType());
		$this->assertEquals("WINDOWS-1250",$r->getContentCharset());
	}

	function _fetch_response($response){
		$ser = serialize($response);
		Files::WriteToFile("response.ser",$ser,$err,$err_str);
		$fetcher = new UrlFetcher("http://127.0.0.1/sources/http/test/response.php",array("max_redirections" => 0));
		//unlink("response.ser"); // s timto smazanim to nefunguje...!?
		return $fetcher;
	}
}

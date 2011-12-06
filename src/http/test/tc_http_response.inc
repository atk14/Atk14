<?
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

	function test_set_location(){
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

	function _fetch_response($response){
		$ser = serialize($response);
		files::write_to_file("response.ser",$ser,$err,$err_str);
		$fetcher = new UrlFetcher("http://127.0.0.1/sources/http/test/response.php");
		//unlink("response.ser"); // s timto smazanim to nefunguje...!?
		return $fetcher;
	}
}

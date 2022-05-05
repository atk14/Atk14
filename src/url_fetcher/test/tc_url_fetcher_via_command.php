<?php
class tc_url_fetcher_via_command extends tc_base {

	function test(){
		$cmd = "nc www.atk14.net 80";
		$f = new UrlFetcherViaCommand($cmd,"http://www.atk14.net/api/en/http_requests/detail/?format=json");
		$this->assertEquals(200,$f->getStatusCode());
		$data = json_decode((string)$f->getContent(),true);
		$this->assertEquals("GET",$data["method"]);

		$cmd = __DIR__ . "/test_response.sh";
		$f = new UrlFetcherViaCommand($cmd,"http://www.example.com/");
		$this->assertEquals(201,$f->getStatusCode());
		$this->assertEquals("TEST!",(string)$f->getContent());
		$this->assertEquals("text/plain",$f->getContentType());

		$cmd = __DIR__ . "/empty_response.sh";
		$f = new UrlFetcherViaCommand($cmd,"http://www.example.com/");
		$this->assertEquals(200,$f->getStatusCode());
		$this->assertEquals("",(string)$f->getContent());
		$this->assertEquals("text/html",$f->getContentType());
	}
}

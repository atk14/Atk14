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

	function test_chunked_encoding(){
		$cmd = __DIR__ . "/chunked_response.sh";
		$f = new UrlFetcherViaCommand($cmd,"http://www.example.com/",array("http_version" => "1.1"));
		$this->assertEquals(200,$f->getStatusCode());
		$this->assertEquals("text/plain",$f->getContentType());
		$this->assertEquals("Hello World!",(string)$f->getContent());
		$this->assertEquals(12,$f->getContentLength());
	}

	function test_broken_chunked_encoding(){
		// chunk-size line is not a valid hex number ("ZZZ") - the decoder can never
		// find a valid chunk boundary, so the stream ends without ever reaching the
		// terminating zero-length chunk; this must be reported as an error rather
		// than silently returning incomplete/garbage content
		$cmd = __DIR__ . "/broken_chunked_response.sh";
		$f = new UrlFetcherViaCommand($cmd,"http://www.example.com/",array("http_version" => "1.1"));
		$this->assertFalse($f->found());
		$this->assertTrue($f->errorOccurred());
		$this->assertEquals(null,$f->getStatusCode());
		$this->assertEquals(null,$f->getContent());
	}

	function test_chunked_encoding_with_size_larger_than_actual_data(){
		// chunk-size line announces 10 (0xa) bytes but only 2 bytes ("Hi") are actually
		// sent before the connection closes without a terminating zero-length chunk -
		// this is a truncated/invalid transfer and must be reported as an error rather
		// than hanging or silently returning the partial content
		$cmd = __DIR__ . "/chunked_response_size_mismatch.sh";
		$f = new UrlFetcherViaCommand($cmd,"http://www.example.com/",array("http_version" => "1.1"));
		$this->assertFalse($f->found());
		$this->assertTrue($f->errorOccurred());
		$this->assertEquals(null,$f->getStatusCode());
		$this->assertEquals(null,$f->getContent());
	}
}

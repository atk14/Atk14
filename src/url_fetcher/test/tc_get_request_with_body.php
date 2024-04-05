<?php
class TcGetRequestWithBody extends TcBase {

	function test(){
		$f = new UrlFetcher();
		$f->fetchContent("http://www.atk14.net/api/en/http_requests/detail/?format=json",array(
			"content" => "TEST",
			"content_type" => "text/plain",
		));
		$this->assertEquals(200,$f->getStatusCode());
		$data = json_decode((string)$f->getContent(),true);

		$this->assertEquals("GET",$data["method"]);
		$this->assertEquals(base64_encode("TEST"),$data["raw_post_data_base64"]);

		$this->assertTrue(in_array(array("name" => "Content-Length", "value" => "4"),$data["headers"]));
	}
}

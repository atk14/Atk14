<?php
class tc_url_fetcher_via_command extends tc_base {

	function test(){
		$cmd = "nc www.atk14.net 80";
		$f = new UrlFetcherViaCommand($cmd,"http://www.atk14.net/api/en/http_requests/detail/?format=json");
		$this->assertEquals(200,$f->getStatusCode());
		$data = json_decode((string)$f->getContent(),true);
		$this->assertEquals("GET",$data["method"]);
	}
}

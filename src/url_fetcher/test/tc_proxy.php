<?php
class TcProxy extends TcBase {

	function test(){
		if(!$this->_is_privoxy_running()){
			file_put_contents("php://stderr","WARNING: privoxy is not running; skip testing");
			$this->assertEquals(1,1);
			return;
		}

		// http
		$uf = new UrlFetcher("http://example.com/",["proxy" => "tcp://127.0.0.1:8118"]);
		$this->assertEquals(200,$uf->getStatusCode());
		$this->assertContains("Example Domain",(string)$uf->getContent());

		// https
		$uf = new UrlFetcher("https://www.atk14.net/api/en/http_requests/detail/?format=json&requested_response_code=417",["proxy" => "tcp://127.0.0.1:8118"]);
		$uf->post(["a" => "b", "c" => "d"]);

		$this->assertEquals(417,$uf->getStatusCode());
		$this->assertEquals("Expectation Failed",$uf->getStatusMessage());

		$data = json_decode($uf->getContent(),true);
		$post_data = base64_decode($data["raw_post_data_base64"]);
		$this->assertEquals("a=b&c=d",$post_data);
	}
	
	function test_privoxy_config(){
		if(!$this->_is_privoxy_running()){
			$this->assertEquals(1,1);
			return;
		}

		$uf = new UrlFetcher("http://config.privoxy.org/",["proxy" => "tcp://127.0.0.1:8118"]);
		$this->assertEquals(200,$uf->getStatusCode());
		$this->assertTrue(!!preg_match("/<title>Privoxy@(ip6-|)localhost<\/title>/",(string)$uf->getContent()));

		$uf = new UrlFetcher("http://config.privoxy.org/");
		$this->assertEquals(200,$uf->getStatusCode());
		$this->assertContains("<title>Privoxy is not being used</title>",(string)$uf->getContent());
	}

	function test_proxy_server_is_down(){
		$uf = new UrlFetcher("http://example.com/",["proxy" => "tcp://127.0.0.1:8888"]); // no proxy server is running on this port
		$this->assertFalse($uf->found());
		$this->assertNull($uf->getStatusCode());
		$this->assertContains("could not connect to proxy server tcp://127.0.0.1:8888",$uf->getErrorMessage());
	}

	function _is_privoxy_running(){
		$uf = new UrlFetcher("http://127.0.0.1:8118/");
		if(@is_null($uf->getStatusCode())){
			return false;
		}
		return true;
	}
}

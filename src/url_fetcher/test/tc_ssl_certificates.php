<?php
class TcSslCertificates extends TcBase {

	function test(){
		// Valid SSL certificate
		$uf = new UrlFetcher("https://test-ev-rsa.ssl.com/");
		$this->assertEquals(200,$uf->getStatusCode());

		// Expired SSL certificate
		try {
			$uf = new UrlFetcher("https://expired-rsa-dv.ssl.com/");
			$this->fail();
		}catch(Exception $e){
			$this->assertTrue(true);
		}
		//
		$uf = new UrlFetcher("https://expired-rsa-dv.ssl.com/",array("verify_peer" => false));
		$this->assertEquals(200,$uf->getStatusCode());

		// Revoked SSL certificate
		try {
			$uf = new UrlFetcher("https://revoked-rsa-dv.ssl.com/");
			$this->fail();
		}catch(Exception $e){
			$this->assertTrue(true);
		}
		//
		$uf = new UrlFetcher("https://revoked-rsa-dv.ssl.com/",array("verify_peer" => false));
		$this->assertEquals(200,$uf->getStatusCode());
	}
}

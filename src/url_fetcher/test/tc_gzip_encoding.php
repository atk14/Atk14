<?php
class TcGzipEncoding extends TcBase {

	function test(){
		// no gzip encoding
		$uf = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/aaa.php");
		$this->assertEquals(true,$uf->found());
		$this->assertNull($uf->getHeaderValue("Content-Encoding"));
		$this->assertTrue(!!preg_match('/^aaa/',$uf->getContent()));
		$this->assertEquals(1000,$uf->getContentLength());
		$this->assertTrue($uf->getContentLength() == $uf->getHeaderValue("Content-Length"));

		// gzip encoding
		$uf = new UrlFetcher("https://jarek.plovarna.cz/unit-testing/aaa.php",["additional_headers" => ["Accept-Encoding: gzip"]]);
		$this->assertEquals(true,$uf->found());
		$this->assertEquals("gzip",$uf->getHeaderValue("Content-Encoding"));
		$this->assertTrue(!!preg_match('/^aaa/',$uf->getContent()));
		$this->assertEquals(1000,$uf->getContentLength());
		$this->assertTrue($uf->getContentLength() > $uf->getHeaderValue("Content-Length"));
	}
}

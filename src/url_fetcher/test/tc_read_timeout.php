<?php
class TcReadTimeout extends TcBase {

	function test(){
		$uf = new UrlFetcher("https://www.atk14.net/api/en/delayed_responses/detail/?delay=0&format=json",array(
			"read_timeout" => 2.0,
		));
		$this->assertTrue($uf->found());
		$this->assertEquals("[]",$uf->getContent());

		$uf = new UrlFetcher("https://www.atk14.net/api/en/delayed_responses/detail/?delay=3&format=json",array(
			"read_timeout" => 2.0,
		));
		$this->assertFalse($uf->found());
		$this->assertEquals("read timeout",$uf->getErrorMessage());

		$uf = new UrlFetcher("https://www.atk14.net/api/en/delayed_responses/detail/?delay=3&format=json",array(
			"read_timeout" => 4.0,
		));
		$this->assertTrue($uf->found());
		$this->assertEquals("[]",$uf->getContent());
	}
}

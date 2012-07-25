<?php
class TcClient extends TcBase{
	function test(){
		$client = new Atk14Client();

		$controller = $client->get("testing/test",array("id" => "123", "format" => "xml"));
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("123",$controller->params->g("id"));
		$this->assertEquals("xml",$controller->params->g("format"));
		$this->assertEquals(true,$controller->request->get());
		$this->assertEquals(false,$controller->request->post());

		$controller = $client->post("testing/test",array("id" => "123", "format" => "xml"));
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("123",$controller->params->g("id"));
		$this->assertEquals("xml",$controller->params->g("format"));
		$this->assertEquals(false,$controller->request->get());
		$this->assertEquals(true,$controller->request->post());

		$controller = $client->post("testing/test","<xml></xml>",array("content_type" => "text/xml"));
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals(true,$controller->params->isEmpty());
		$this->assertEquals(false,$controller->request->get());
		$this->assertEquals(true,$controller->request->post());
		$this->assertEquals("text/xml",$controller->request->getContentType());
		$this->assertEquals("<xml></xml>",$controller->request->getRawPostData());
	}
}

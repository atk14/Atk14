<?
class tc_main extends tc_base{
	function test_index(){
		$client = new Atk14Client();

		$controller = $client->get("main/index");
		$this->assertEquals(200,$controller->response->getStatusCode());
	}

	function test_error404(){
		$client = new Atk14Client();

		$controller = $client->get("main/error404");
		$this->assertEquals(404,$controller->response->getStatusCode());
	}
}

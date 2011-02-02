<?
class tc_application extends tc_base{

	/**
	* Usually the meaningful index action is provided by a inheritor.
	* So good idea should be to service "HTTP 404 Not Found" response on base controller`s index.
	*/
	function test_index(){
		$client = new Atk14Client();

		$controller = $client->get("application/index");
		$this->assertEquals(404,$controller->response->getStatusCode());
	}

	function test_error404(){
		$client = new Atk14Client();

		$controller = $client->get("application/non_existing_action");
		$this->assertEquals(404,$controller->response->getStatusCode());

		$controller = $client->get("application/error404");
		$this->assertEquals(404,$controller->response->getStatusCode());
	}
	
}

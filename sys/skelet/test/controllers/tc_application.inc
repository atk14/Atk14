<?
class tc_application extends tc_base{
	function test_index(){
		$client = new Atk14Client();

		$controller = $client->get("application/index");
		$this->assertEquals(404,$controller->response->getStatusCode());
	}
}

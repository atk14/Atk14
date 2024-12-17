<?php
class TcWalking extends TcBase {

	function test(){
		$client = $this->client;

		// step 1

		$client->get("walking/walk");
		$this->assertEquals(200,$client->getStatusCode());

		$content = $client->getContent();
		$this->assertStringContains("<h1>Step #1</h1>",$content);
		$this->assertStringContains("current_step_index: 0",$content);
		$this->assertStringContains("current_step_name: step1",$content);

		// step 2

		$client->post("walking/walk",array("name" => "Captain Bobek"));
		$this->assertEquals(303,$client->getStatusCode());

		$location = $client->getLocation();
		$route_data = Atk14Url::RecognizeRoute($location);
		$params = $route_data["get_params"];

		$client->get("walking/walk",$params);
		$content = $client->getContent();
		$this->assertStringContains("<h1>Step #2</h1>",$content);
		$this->assertStringContains("current_step_index: 1",$content);
		$this->assertStringContains("current_step_name: step2",$content);
		$this->assertStringContains("returned_by_step1: step1 finished successfully",$content);
		$this->assertStringContains("step1.name: Captain Bobek",$content);

		// step 3

		$params["straight_to_step3"] = "1";

		$client->get("walking/walk",$params);
		$content = $client->getContent();
		$this->assertStringContains("<h1>Step #3</h1>",$content);
		$this->assertStringContains("current_step_index: 2",$content);
		$this->assertStringContains("current_step_name: step3",$content);
		$this->assertStringContains("returned_by_step1: step1 finished successfully",$content);
		$this->assertStringContains("returned_by_step2: step2 finished gracefully",$content);
		$this->assertStringContains("step1.name: Captain Bobek",$content);
	}
}

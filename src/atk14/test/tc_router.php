<?php
class TcRouter extends TcBase {

	function test(){
		$router = new DefaultRouter();
		$this->assertEquals("",$router->namespace);

		$router = new DefaultRouter(array("namespace" => "admin"));
		$this->assertEquals("admin",$router->namespace);
	}
}

<?php
class TcFormInUniverseNamespace extends TcBase{
	function test(){
		$client = new Atk14Client();
		$controller = $client->get("universe/en/main/index");
		$this->assertEquals("I_am_the_form_in_default_namespace",$controller->form->identifier);
	}
}

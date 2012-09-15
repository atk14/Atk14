<?php
class TcFormInDefaultNamespace extends TcBase{
	function test(){
		$client = new Atk14Client();
		$controller = $client->get("main/index");
		$this->assertEquals("I_am_the_form_in_default_namespace",$controller->form->identifier);
	}
}

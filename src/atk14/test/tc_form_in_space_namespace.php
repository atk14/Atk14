<?php
class TcFormInSpaceNamespace extends TcBase{
	function test(){
		$client = new Atk14Client();
		$controller = $client->get("space/en/main/index");
		$this->assertEquals("I_am_the_form_in_space_namespace",$controller->form->identifier);
	}
}

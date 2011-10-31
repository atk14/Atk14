<?php
class tc_base extends tc_super_base{
	function _run_action($path,$options = array()){
		list($controller,$action) = explode("/",$path);
		$response = Atk14Dispatcher::ExecuteAction($controller,$action);
		return $response->buffer->toString();
	}

	/**
	* Converts an HTML snippet to instance of XMole.
	*/
	function _html2xmole($html_output){
		$xm = new XMole();
		$xml = '<'.'?xml version="1.0" encoding="UTF-8"?'.'><html>'.$html_output.'</html>';

		// nahrazeni pojmenovanych HTML entit za jejich ciselne ekvivalenty
		// xml parser totiz havaroval na chybe "Undeclared entity error"
		$xml = strtr($xml,array(
			"&uArr;" => "&#8593;",
		));

		$stat = $xm->parse($xml);
		$this->assertEquals("",(string)$xm->get_error_message());
		$this->assertEquals(true,$stat);
		return $xm;
	}
}

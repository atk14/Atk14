<?php
class TcBase extends TcSuperBase{
	function setUp(){
		global $dbmole;

		$this->dbmole = $dbmole;
		$this->dbmole->begin();

		$this->client = new Atk14Client();
	}

	function tearDown(){
		$this->dbmole->rollback();
	}

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

		$xml = strtr($xml,array(
			"&" => "&amp;",
		));

		$stat = $xm->parse($xml);
		$this->assertEquals("",(string)$xm->get_error_message());
		$this->assertEquals(true,$stat);
		return $xm;
	}
}

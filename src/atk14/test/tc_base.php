<?php
#[\AllowDynamicProperties]
class TcBase extends TcSuperBase{

	// Without this the test TcSession::test_initialization() fails in PHPUnit 4.8
	protected $backupGlobals = false;

	function setUp(){
		global $dbmole;

		$this->dbmole = $dbmole;
		$this->dbmole->begin();

		$this->client = new Atk14Client();
	}

	function tearDown(){
		$this->dbmole->rollback();
	}

	function _run_action($path,$options = array(),&$response = null){
		$ar = explode("/",$path);
		if(sizeof($ar)==3){
			//$response = Atk14Dispatcher::ExecuteAction($ar[0],$ar[1]);
			$response = Atk14Dispatcher::ExecuteAction($ar[1],$ar[2],array("namespace" => $ar[0]));
		}else{
			$response = Atk14Dispatcher::ExecuteAction($ar[0],$ar[1],array("namespace" => ""));
		}
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

<?php
class TcRobot extends TcBase{
	function test(){
		global $ATK14_GLOBAL;
		$logger = $ATK14_GLOBAL->getLogger();
		$this->assertTrue(!!preg_match('/test.log$/',$logger->_default_log_file));

		require_once(dirname(__FILE__)."/robots/application_robot.php");
		require_once(dirname(__FILE__)."/robots/dummy_robot.php");
		$robot = new DummyRobot();

		// the default logger was changed by instantiating a robot
		$logger = $ATK14_GLOBAL->getLogger();
		$this->assertTrue(!!preg_match('/robots.log$/',$logger->_default_log_file));
	}
}

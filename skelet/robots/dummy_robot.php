<?php
/**
 * One can execute this robot this way:
 *
 *  $ ./scripts/robot_runner dummy
 *
 * Every robot`s log is at ./log/robots.log
 */
class DummyRobot extends ApplicationRobot{
	function run(){
		$this->logger->info("I'm quite a dummy robot");
		
		// some useful information
		$creatures_count = $this->dbmole->selectInt("SELECT COUNT(*) FROM creatures");
		$this->logger->info("there are $creatures_count creatures so far");
	}
}

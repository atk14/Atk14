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
		$this->logger->info("I'm a dummy robot and I do nothing");

		// You have access to
		//	$this->logger
		// 	$this->dbmole
		//	$this->mailer
		//	
		// For more information visit http://book.atk14.net/czech/robots/
	}
}

<?php
/**
 * Empty class.
 *
 * Is used by robots, scripts executed from CLI.
 *
 * @package Atk14
 * @subpackage Core
 * @filesource
 */

/**
 * Empty class.
 *
 * Is used by robots, scripts executed from CLI.
 *
 * @package Atk14
 * @subpackage Core
 */
class Atk14Robot{
	var $dbmole = null;
	var $logger = null;
	var $mailer = null;

	function __construct(){
		$this->dbmole = &$GLOBALS["dbmole"];
		$robot_name = String::ToObject(get_class($this))->underscore()->gsub('/_robot$/','');
		$this->logger = new Logger("$robot_name");

		$this->mailer = Atk14Mailer::GetInstance(array(
			"namespace" => "",
			"logger" => $this->logger,
		));

		$this->logger->start();

		$this->before_run();
		$this->run();
		$this->after_run();

		$this->logger->stop();
		$this->logger->flush_all();
	}

	function before_run(){

	}

	function run(){
		
	}

	function after_run(){

	}

	
}

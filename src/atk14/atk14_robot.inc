<?php
/**
 * Base class for by robots, scripts executed from CLI.
 *
 * @package Atk14
 * @subpackage Core
 * @filesource
 */

/**
 * Base class for by robots, scripts executed from CLI.
 *
 * @package Atk14
 * @subpackage Core
 */
class Atk14Robot{
	var $dbmole = null;
	var $logger = null;
	var $mailer = null;
	var $default_log_file = "";

	function __construct(){
		global $ATK14_GLOBAL;

		if(!$this->default_log_file){
			$this->default_log_file = $ATK14_GLOBAL->getApplicationPath()."/../log/robots.log";
		}
		
		$this->dbmole = &$GLOBALS["dbmole"];
		$robot_name = String::ToObject(get_class($this))->underscore()->gsub('/_robot$/','');
		$this->logger = new Logger("$robot_name",array("default_log_file" => $this->default_log_file));

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

<?php
/**
 * Base class for by robots, scripts executed from CLI.
 *
 * @package Atk14
 * @subpackage Core
 * @filesource
 */

/**
 * Base class for robots, scripts executed from CLI.
 *
 * Basic robot is created as descendant of Atk14Robot and must contain one run method.
 *
 * Example of minimal robot. Name of the script should be '...application_dir/robots/some_counting_robot'.
 * <code>
 * class SomeCountingRobot extends Atk14Robot {
 * 	function run() {
 * 		$count = $this->dbmole->selectSingleValue("SELECT count(*) FROM customers");
 * 		...
 * 	}
 * }
 * </code>
 *
 * There are two more methods that are used once each robot is executed.
 *
 * Method ({@link Atk14Robot::beforeRun()}) is executed before the robots {@link run()} method.
 * Method {@link afterRun()} is executed after the robots {@link run()} method.
 *
 *
 * Run the robot with command robot_runner
 * <code>
 * ./scripts/robot_runner some_counting
 * </code>
 *
 * or
 * <code>
 * ./scripts/robot_runner some_counting_robot
 * </code>
 *
 * @package Atk14
 * @subpackage Core
 */
class Atk14Robot{
	/**
	 * Connection to database
	 *
	 * @var DbMole
	 */
	var $dbmole = null;

	/**
	 * Link to application logger.
	 *
	 * @var Logger
	 */
	var $logger = null;

	/**
	 * Link to mailer.
	 *
	 * Using this variable emails can be sent from a robot.
	 *
	 * Note: in fact this is a Atk14MailerProxy member TODO: to be explained
	 *
	 * @var Atk14Mailer
	 */
	var $mailer = null;

	/**
	 * path to log file
	 *
	 * @var string
	 */
	var $default_log_file = "";

	/**
	 * Constructor
	 *
	 */
	function __construct(){
		global $ATK14_GLOBAL;

		if(!$this->default_log_file){
			$this->default_log_file = $ATK14_GLOBAL->getApplicationPath()."/../log/robots.log";
		}
		
		$this->dbmole = &$GLOBALS["dbmole"];
		$robot_name = String::ToObject(get_class($this))->underscore()->gsub('/_robot$/','');
		$this->logger = new Logger("$robot_name",array("default_log_file" => $this->default_log_file));

		$this->mailer = Atk14MailerProxy::GetInstance(array(
			"namespace" => "",
			"logger" => $this->logger,
		));

		$this->logger->start();

		Lock::Mklock($robot_name,$this->logger);

		$this->beforeRun();
		$this->run();
		$this->afterRun();

		Lock::Unlock($robot_name,$this->logger);

		$this->logger->stop();
		$this->logger->flush_all();
	}

	/**
	 * Method beforeRun is executed before the main method
	 *
	 */
	function beforeRun(){

	}

	/**
	 * Main method.
	 *
	 */
	function run(){
		
	}

	/**
	 * Method afterRun is executed after the main method.
	 *
	 */
	function afterRun(){

	}

}

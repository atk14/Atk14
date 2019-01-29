<?php
/**
 * Base class for by robots, scripts executed from CLI.
 *
 * @filesource
 */

/**
 * Base class for robots, scripts executed from CLI.
 *
 * Basic robot is created as descendant of Atk14Robot and must contain one run method.
 *
 * Example of minimal robot. Name of the script should be '...application_dir/robots/some_counting_robot'.
 * ```
 * class SomeCountingRobot extends Atk14Robot {
 * 	function run() {
 * 		$count = $this->dbmole->selectSingleValue("SELECT count(*) FROM customers");
 * 		...
 * 	}
 * }
 * ```
 *
 * There are two more methods that are used once each robot is executed.
 *
 * Method ({@link Atk14Robot::beforeRun()}) is executed before the robots {@link Atk14Robot::run()} method.
 * Method {@link Atk14Robot::afterRun()} is executed after the robots {@link Atk14Robot::run()} method.
 *
 *
 * Run the robot with command robot_runner
 * ```
 * ./scripts/robot_runner some_counting
 * ```
 *
 * or
 * ```
 * ./scripts/robot_runner some_counting_robot
 * ```
 *
 * @package Atk14\Core
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
	 * Note: in fact this is a Atk14MailerProxy member
	 *
	 * @todo explain
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
	 * Whether lock the current process to prevent another start of the same robot or not
	 *
	 * @var boolean
	 */
	var $locking_enabled = true;

	/**
	 * Whether this robot may be executed or not
	 *
	 * For instance in the beforeRun() this variable can be set to false in certain circumstances.
	 */
	var $execute_robot = true;

	/**
	 * Robot name
	 *
	 * e.g. "article_importer"
	 *
	 * @var start
	 */
	var $robot_name = "";

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
		$robot_name = String4::ToObject(get_class($this))->underscore()->gsub('/_robot$/','');
		$this->robot_name = $robot_name;
		$this->logger = new Logger("$robot_name",array(
			"default_log_file" => $this->default_log_file,
			"automatically_log_to_stdout_on_terminal" => true,
		));

		$ATK14_GLOBAL->setLogger($this->logger);

		$this->mailer = Atk14MailerProxy::GetInstance(array(
			"namespace" => "",
			"logger" => $this->logger,
		));
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
		throw new Exception(sprintf("method %s::run() has to be defined",get_class($this)));
	}

	/**
	 * Method afterRun is executed after the main method.
	 *
	 */
	function afterRun(){

	}

	/**
	 *
	 * @ignore
	 */
	final function __runRobot(){
		$this->logger->start();

		$this->locking_enabled && Lock::Mklock($this->robot_name,$this->logger);

		$this->beforeRun();
		$this->execute_robot && $this->run();
		$this->afterRun();

		$this->locking_enabled && Lock::Unlock($this->robot_name,$this->logger);

		$bytes = memory_get_peak_usage(true);
		if($bytes>(1024*1024)){ $bytes = number_format($bytes/(1024 * 1024),2,".",",")."MB"; }
		elseif($bytes>1024){ $bytes = number_format($bytes/1024,2,".",",")."kB"; }
		else{ $bytes = "$bytes Bytes"; }
		$msg = "real peak memory usage: ".$bytes;

		$this->logger->stop($msg);
		$this->logger->flush_all();
	}
}

<?php
/**
 * Class for events logging.
 *
 * @filesource
 */

defined("LOGGER_DEFAULT_LOG_FILE") || define("LOGGER_DEFAULT_LOG_FILE","/tmp/logger.log");
defined("LOGGER_DEFAULT_NOTIFY_EMAIL") || define("LOGGER_DEFAULT_NOTIFY_EMAIL",""); // "john@doe.com"

/**
 *
 * 3 .. warn+
 * 4 .. error
 */
defined("LOGGER_MIN_LEVEL_FOR_EMAIL_NOTIFICATION") || define("LOGGER_MIN_LEVEL_FOR_EMAIL_NOTIFICATION",30);

/**
 * -1 .. debug 
 */
defined("LOGGER_NO_LOG_LEVEL") || define("LOGGER_NO_LOG_LEVEL",-30);

/**
 * Class for events logging.
 *
 * ## Basic usage
 *
 * Create a new Logger instance
 * ```
 * $logger = new Logger("application_mark");
 * $logger->start();
 * ```
 * Send a message to the logger.
 * ```
 * $logger->info("some message");
 * ```
 * Finish logging
 * ```
 * $logger->stop();
 * ```
 *
 * ### Message priorities
 *
 * There are predefined message levels, each is assigned a priority.
 * These levels have special methods to send a message.
 * ```
 * $logger->debug("a debug message");
 * $logger->info("some message");
 * $logger->warn("a warning message");
 * $logger->error("an error message");
 * $logger->security("a security concerning message");
 * ```
 *
 * If some specific priority is needed, use {@link put_log} method.
 * ```
 * $logger->put_log("unknown exception occured", 150);
 * ```
 * Set constant LOGGER_MIN_LEVEL_FOR_EMAIL_NOTIFICATION to specify from which priority messages are sent te email.
 *
 * In case we don't need START and STOP marks to show in the STDOUT, we create the logger instance this way:
 * ```
 * $logger = new Logger("application_mark",array("disable_start_and_stop_marks" => true));
 * ```
 *
 *
 * ## Older way of usage
 * ```
 * $logger = new Logger();
 * $logger->set_prefix("application_mark");
 *
 * // by default calls prepared_log("start") and prepared_log("stop") output to stdout
 * // sets silent mode - nothing is output to stdout (nepouzije se echo)
 * $logger->set_silent_mode();
 * $logger->prepared_log("start");
 * ```
 *
 * Then in application:
 * ```
 * $logger->put_log("some important application message",0);
 * ```
 *
 * Closing and flushing log
 * ```
 * $logger->prepared_log("stop");
 * $logger->flush_all();
 * ```
 *
 * The file where events are logged is defined by constant LOGGER_DEFAULT_LOG_FILE
 * ```
 * define("LOGGER_DEFAULT_LOG_FILE","/home/yarri/www/gr/sys/log/log");
 * ```
 *
 * @package Atk14\InternalLibraries
 * @filesource
 */

class Logger{
	/**
	 * Application mark
	 *
	 * @access private
	 * @var string
	 */
	var $_prefix = "";

	/**
	 * Name of output file
	 *
	 * @access private
	 * @var string
	 */
	var $_log_file;

	/**
	 * Default filename where to output log messages
	 *
	 * @todo should be private.
	 * @todo correct tests in atk14/test/tc_robot.php. they use this property
	 */
	var $_default_log_file;

	/**
	 * Internal events storage.
	 *
	 * @access private
	 * @var array
	 */
	var $_log_store = array();

	/**
	 * @access private
	 */
	var $_log_store_whole = array();
	
	/**
	 * @access private
	 */
	var $_silent_mode = true;

	/**
	 * Flag to control logging of start and stop messages.
	 *
	 * @var boolean
	 */
	private $_disable_start_and_stop_marks = false;

	/**
	 * Events with $_no_log_level priority and lower are not sent to output.
	 *
	 * @access private
	 */
	var $_no_log_level;

	/**
	 * Threshold to trigger sending notification
	 *
	 * When a log message has priority same or higher than $_notify_level, notification is sent to email
	 * Defailt value is 30 or can be overridden with constant {@link LOGGER_MIN_LEVEL_FOR_EMAIL_NOTIFICATION}
	 *
	 * @var integer
	 */
	private $_notify_level;

	/**
	 * Email address for sending logged messages
	 *
	 * @var string
	 */
	private $_notify_email;

	/**
	 *
	 * @var string
	 */
	private $_default_notify_email;

	/**
	 * @access private
	 */
	var $_notify_level_reached = false;

	/**
	 * @access private
	 */
	var $_my_pid;

	/**
	 * Timestamp of logging start
	 *
	 * Value is set during {@link prepared_log("start") prepared_log()} call
	 *
	 * @var internal
	 */
	private $_started_at_time = null;

	/**
	 * @access private
	 */
	var $_log_to_file = true;

	/**
	 * @access private
	 */
	var $_log_to_stdout = false;

	/**
	 * Flag determining if the logged messages are also sent to stdout when a calling script is executed in command line
	 *
	 * Default value is false
	 *
	 * @var boolean
	 */
	private $_automatically_log_to_stdout_on_terminal = false;

	/**
	 * Table with recognized error levels where string labels are assigned to its integer values.
	 *
	 * @var array
	 */
	private $_levels = array(
		"-2" => "debug++",
		"-1" => "debug",
		"0" => "info",
		"1" => "info++",
		"2" => "warn",
		"3" => "warn++",
		"4" => "error",
		"5" => "security",
		"6" => "security++",
	);

	/**
	 * Color hex code assigned to recognized error levels
	 *
	 * @var array
	 */
	private $_colors = array(
		"debug" => "#555555",
		"info" => "#000000",
		"warn" => "#c66905",
		"error" => "#d00b00",
		"security" => "#d00b00",
	);

	/**
	 * Constructor
	 *
	 * @param string $prefix application_mark
	 * @param array $options
	 * - disable_start_and_stop_marks (false) - whether start and stop marks show up in output
	 * - default_log_file - filename where to write logs
	 * - log_to_stdout (false) - log messages to STDOUT
	 * - log_to_file - log messages to a log file; by default it is true, when log_to_stdout is false
	 * - automatically_log_to_stdout_on_terminal (false) - log messages to a log file and also to STDOUT when we are on TERMINAL
	 * - default_notify_email
	 */
	function __construct($prefix_or_options = "",$options = array()){
		if(is_array($prefix_or_options)){
			$options = $prefix_or_options;
		}else{
			$options["prefix"] = $prefix_or_options;
		}
		
		$options = array_merge(array(
			"prefix" => "",
			"disable_start_and_stop_marks" => false,
			"default_log_file" => LOGGER_DEFAULT_LOG_FILE,
			"log_to_stdout" => false,
			"log_to_file" => null,
			"automatically_log_to_stdout_on_terminal" => false,
			"default_notify_email" => LOGGER_DEFAULT_NOTIFY_EMAIL,
		),$options);

		if(is_null($options["log_to_file"])){
			$options["log_to_file"] = !$options["log_to_stdout"];
		}

		$this->_default_log_file = $options["default_log_file"];

		$this->_reset_configuration();
		$this->_my_pid = posix_getpid();

		$this->set_prefix($options["prefix"]);
		$this->_log_to_stdout = $options["log_to_stdout"];
		$this->_log_to_file = $options["log_to_file"];
		$this->_automatically_log_to_stdout_on_terminal = $options["automatically_log_to_stdout_on_terminal"];
		$this->_disable_start_and_stop_marks = $options["disable_start_and_stop_marks"];
		$this->_default_notify_email = $options["default_notify_email"];
	}

	/**
	 * Returns output filename.
	 *
	 * @return string
	 */
	function get_log_file(){ return $this->_log_file; }

	/**
	 * Return log level priority
	 *
	 * @return integer
	 */
	function get_no_log_level(){ return $this->_no_log_level; }

	/**
	 * Get level for sending email notifications
	 *
	 * @return integer
	 */
	function get_notify_level(){ return $this->_notify_level; }

	/**
	 * Get email address for email notifications.
	 *
	 * @return string
	 */
	function get_notify_email(){
		return strlen($this->_notify_email) ? $this->_notify_email : $this->_default_notify_email; 
	}

	/**
	 * Prefix setup
	 *
	 * @internal This method also initializes $_notify_level, $_notify_email, $_no_log_level and $_log_file
	 * @param string $prefix application_mark
	 */
	function set_prefix($prefix){
		global $LOGGER_CONFIGURATION;
		settype($prefix,"string");
		$this->_prefix = $prefix;

		$this->_determin_configuration();
	}

	function get_prefix(){
		return $this->_prefix;
	}

	/**
	 * @ignore
	 */
	private function _determin_configuration(){
		$this->_reset_configuration();
		for($i=0;$i<=strlen($this->_prefix);$i++){
			$this->_find_configuration(substr($this->_prefix,0,$i)."*");
		}
		$this->_find_configuration($this->_prefix);
	}

	/**
	 * @ignore
	 */
	private function _find_configuration($prefix){
		global $LOGGER_CONFIGURATION;

		if(!isset($LOGGER_CONFIGURATION)){ $LOGGER_CONFIGURATION = array();}

		if(isset($LOGGER_CONFIGURATION[$prefix])){

			if(isset($LOGGER_CONFIGURATION[$prefix]['notify_level'])){ $this->_notify_level = $this->level_to_int($LOGGER_CONFIGURATION[$prefix]['notify_level']); }
			if(isset($LOGGER_CONFIGURATION[$prefix]['notify_email'])){ $this->_notify_email = (string)$LOGGER_CONFIGURATION[$prefix]['notify_email']; }
			if(isset($LOGGER_CONFIGURATION[$prefix]['no_log_level'])){ $this->_no_log_level = $this->level_to_int($LOGGER_CONFIGURATION[$prefix]['no_log_level']); }
			if(isset($LOGGER_CONFIGURATION[$prefix]['log_file'])){$this->_log_file = (string)$LOGGER_CONFIGURATION[$prefix]['log_file']; }

			return true;
		}

		return false;
	}

	/**
	 * Resets configuration to default values.
	 *
	 */
	private function _reset_configuration(){
		$this->_no_log_level = LOGGER_NO_LOG_LEVEL;
		$this->_notify_level = LOGGER_MIN_LEVEL_FOR_EMAIL_NOTIFICATION;
		$this->_notify_email = $this->_default_notify_email;
		$this->_log_file = $this->_default_log_file;
	}

	/**
	 * Returns specified log level as integer
	 *
	 * ```
	 * $l_int = $logger->level_to_int("warn"); // returns 2
	 * $l_int = $logger->level_to_int(2); // returns 2
	 * $l_int = $logger->level_to_int(30); // (unknown level) returns 30
	 * ```
	 *
	 * @param string $level
	 * @return int integer representation of log level
	 */
	function level_to_int($level){
		foreach($this->_levels as $key => $value){
			if(strcmp("$level",$key)==0 || strcmp("$level",$value)==0){ return (int)$key; }
		}
		return (int)$level;
	}

	/**
	 * Returns specified log level as its name
	 *
	 * ```
	 * $l_str = $logger->level_to_str("info"); // returns "info"
	 * $l_str = $logger->level_to_str(2); // returns "warn"
	 * $l_str = $logger->level_to_str("unknown"); // (unknown level) returns "unknown"
	 * ```
	 *
	 * @param string|int $level
	 * @return string name of log level
	 */
	function level_to_str($level){
		foreach($this->_levels as $key => $value){
			if(strcmp("$level",$key)==0 || strcmp("$level",$value)==0){ return $value; }
		}
		return "$level";
	}

	/**
	 * Returns rgb color hex code that is associated with an error level
	 *
	 * ```
	 * $color = $this->level_to_color("error");
	 * $color = $this->level_to_color(4);
	 * ```
	 * This
	 * ```
	 * echo $color; // "#d00b00"
	 * ```
	 * returns #d00b00
	 *
	 * @param integer|string $level
	 * @return string color hex code
	 * @see self::$_colors
	 */
	function level_to_color($level){
		if(is_numeric($level)){ $level = $this->level_to_str($level); }
		$level = preg_replace('/\+*$/','',$level); // "warn++" -> "warn"
		return $this->_colors[$level];
	}


	/**
	 * Sets silent mode.
	 *
	 * In silent mode nothing is output to stdout.
	 * Can be set to "loud" by passing false as a parameter.
	 *
	 * @param boolean $mode false => loud mode, default value is true
	 * @return int 0
	 */
	function set_silent_mode($mode = true){
		settype($mode,"boolean");
		$this->_silent_mode = $mode;
		return 0;
	}


	/**
	 * Flushes all logged events to output
	 *
	 * Events are stored in internal array. Call this method in case you want to see them in output.
	 *
	 * @return int 0
	 */
	function flush(){
		if(sizeof($this->_log_store)==0){ return 0; }

		$_log_file_existed = file_exists($this->_log_file);

		if($this->_log_to_file){
		}

		if(!$this->_notify_level_reached){
			foreach($this->_log_store as $rec){
				if($rec['log_level']>=$this->_notify_level){
					$this->_notify_level_reached = true;
					break;
				}
			}
		}

		$fp = null;

		foreach($this->_log_store as $rec){

			$this->_log_store_whole[] = $rec;

			if(!$this->_notify_level_reached && $rec['log_level']<=$this->_no_log_level){
				continue;
			}

			$str = $this->_build_message($rec);
			if($this->_log_to_stdout){
				echo $str;
			}
			if($this->_log_to_file){
				if(!$fp){
					$fp = fopen($this->_log_file,"a");
				}
				fwrite($fp,$str,strlen($str));
			}

			if($this->_automatically_log_to_stdout_on_terminal && !$this->_log_to_stdout && posix_isatty(STDOUT)){
				echo $str; // TODO: colorize
			}
		}

		if($fp){
			fclose($fp);
		}

		if($this->_log_to_file && !$_log_file_existed && !preg_match('/^[a-z]+:\/\//i',$this->_log_file)){ // it filters out php://stdout...
			// when the log file was just created by apache user,
			// it needs to be also writable by another user
			// TODO: this needs to be considered carefully
			chmod($this->_log_file,0666); 
		}
		
		$this->_log_store = array();

		return 0;
	}

	/**
	 * @ignore
	 */
	private function _build_message($rec,&$html_output = ""){
		$html_output = "";

		if(!is_bool(strpos($rec['log'],"\n"))){
			$_ar = explode("\n",$rec['log']);
			$rec['log'] = "";
			for($i=0;$i<sizeof($_ar);$i++){
				$rec['log']	.= "\n\t".$_ar[$i];
			}
		}

		$log_level = "";
		if($rec['log_level']<0){
			$log_level = strtolower($this->level_to_str($rec['log_level'])).": "; // "debug: "
		}elseif($rec['log_level']>0){
			$log_level = strtoupper($this->level_to_str($rec['log_level'])).": ";
		}

		$out = "$rec[date] $rec[prefix][$this->_my_pid]: $log_level$rec[log]";

		$html_output = sprintf('<code style="color: %s;">%s</code>',$this->level_to_color($rec["log_level"]),htmlentities($out))."\n";

		return $out."\n";
	}

	/**
	 * Flushes events to output and also to notify email.
	 *
	 * @return int 0
	 * @todo some more info about notify emails
	 */
	function flush_all(){
		$this->flush();
		if($this->_notify_level_reached && $this->_notify_email!=""){
			$this->_send_email_notification();
		}
		$this->_log_store_whole = array();
		return 0;
	}

	/**
	 * Alias to flush_all
	 *
	 * @return int 0
	 * @see flush_all()
	 */
	function flushAll(){ return $this->flush_all(); }

	/**
	 * Alias method for outputing message debug level.
	 *
	 * @see put_log()
	 * @param string $log message to output
	 */
	function debug($log){ $this->put_log($log,-1); }

	/**
	 * Alias method for outputing message with info level.
	 *
	 * @see put_log()
	 * @param string $log message to output
	 */
	function info($log){ $this->put_log($log,0); }
	
	/**
	 * Alias method for outputing message with warning level.
	 *
	 * @see put_log()
	 * @param string $log message to output
	 */
	function warn($log){ $this->put_log($log,2); }

	/**
	 * Alias method for outputing message with error level.
	 *
	 * @see put_log()
	 * @param string $log message to output
	 */
	function error($log){ $this->put_log($log,4); }

	/**
	 * Alias method for outputing message with security level.
	 *
	 * @see put_log()
	 * @param string $log message to output
	 */
	function security($log){ $this->put_log($log,5); }

	/**
	 * Logs a message to a given level.
	 *
	 * It is recommended to use methods {@link error()}, {@link warn()}, {@link info()}, {@link debug()}
	 *
	 * @param string $log logged message
	 * @param int $log_level level at which the message is logged
	 * @return int 0
	 */
	function put_log($log,$log_level = 0){
		$this->_put_log($log,$log_level);
		return 0;
	}

	/**
	 * @ignore
	 */
	private function _put_log($log,$log_level = 0){
		settype($log,"string");
		$log_level = $this->level_to_int($log_level);

		$rec = array(
			"date" => date("Y-m-d H:i:s"),
			"prefix" => $this->_prefix,
			"log_level" => $log_level,
			"log" => $log
		);

		$this->_log_store[] = $rec;
	}

	/**
	 * Starts logging.
	 *
	 * Prefix/application_mark can be defined
	 *
	 * @param string $prefix application_mark
	 */
	function start($prefix = "",$message = ""){
		settype($prefix,"string");
		if(strlen($prefix)>0){ $this->set_prefix($prefix); }
		if(!$this->_disable_start_and_stop_marks){
			$this->prepared_log("start",$message);
		}
	}

	/**
	 * Stops logging
	 *
	 * Also flushes all event to output
	 */
	function stop($message = ""){
		if(!$this->_disable_start_and_stop_marks){
			$this->prepared_log("stop",$message);
		}
		$this->flush_all();
	}

	/**
	 * Method for starting and stopping logger.
	 *
	 * Preferred methods to call are {@link start()} and {@link stop()}
	 *
	 * @todo explain @param $style
	 * @return int 0
	 */
	function prepared_log($style,$message = ""){
		settype($style,"string");
		switch(strtolower($style)){
			case "start":
				$rec = $this->_put_log("START".($message ? ", $message" : ""));
				$this->_started_at_time = $this->_get_microtime();
				if(!$this->_silent_mode){
					echo $this->_build_message($rec);
				}
				$this->flush();
				break;
			case "stop":
				$_log = "STOP";
				if(isset($this->_started_at_time)){
					$_stopped = $this->_get_microtime();
					$_runing_time = $_stopped - $this->_started_at_time;
					$_minutes = (floor($_runing_time/60.0));
					$_log .= sprintf(", running time: %d min %0.2f sec",$_minutes,($_runing_time-($_minutes*60)));
				}
				if($message){
					$_log .= ", $message";
				}
				$rec = $this->_put_log($_log);
				if(!$this->_silent_mode){
					echo $this->_build_message($rec);
				}
				break;
		}
		return 0;
	}

	/**
	 * @ignore
	 */
	function _send_email_notification(){
		$notify_email = $this->get_notify_email();

		if(!strlen($notify_email)){ return;}
		if(!sizeof($this->_log_store_whole)){ return; }

		$max_level = null;
		foreach($this->_log_store_whole as $rec){
			if(!isset($max_level) || $rec["log_level"]>$max_level){ $max_level = $rec["log_level"]; }
		}

		$output = "";
		$output .= "prefix: $this->_prefix\n";
		$output .= "pid: $this->_my_pid\n";
		$output .= sprintf("max_level: %s (%s)\n",$this->level_to_str($max_level),$max_level);
		$output .= "\n";

		$html = '<html><body><pre>'.$output;
		
		foreach($this->_log_store_whole as $rec){	
			// TODO: vymyslet tady nejaky uspornejsi format (bez prefixu a pidu)
			$output .= $this->_build_message($rec,$html_snippet);
			$html .= $html_snippet;
		}

		$html .= '</pre></body></html>';

		$ar = sendhtmlmail(array(
			"plain" => $output,
			"html" => $html,
			"subject" => "log report: $this->_prefix, ".date("Y-m-d H:i:s"),
			"to" => $notify_email,
			"charset" => "UTF-8",
		));

		return $ar;
	}

	/**
	 * @ignore
	 */
	private function _get_microtime(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}

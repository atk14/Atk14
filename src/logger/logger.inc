<?php
/**
 * Class for events logging.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */

/**
 * Class for events logging.
 *
 * New way of usage
 * <code>
 * $logger = new logger("application_mark");
 * $logger->start();
 * </code>
 *
 *
 * Then in application code
 * <code>
 * $logger->info("a message");
 * $logger->debug("a message");
 * $logger->warn("a message");
 * $logger->error("a message");
 * </code>
 *
 * Finish logging
 * <code>
 * $logger->stop();
 * </code>
 *
 * In case we don't need START and STOP marks to show in the output, we create the logger instance this way:
 * <code>
 * $logger = new logger("application_mark",array("disable_start_and_stop_marks" => true));
 * </code>
 *
 *
 * Older way of usage
 * <code>
 * $logger = new logger();
 * $logger->set_prefix("application_mark");
 *
 * // by default calls prepared_log("start") and prepared_log("stop") output to stdout
 * // sets silent mode - nothing is output to stdout (nepouzije se echo)
 * $logger->set_silent_mode();
 * $logger->prepared_log("start");
 * </code>
 *
 * Then in application:
 * <code>
 * $logger->put_log("some important application message",0);
 * </code>
 *
 * Closing and flushing log
 * <code>
 * $logger->prepared_log("stop");
 * $logger->flush_all();
 * </code>
 *
 * The file where events are logged is defined by constant LOGGER_DEFAULT_LOG_FILE
 * <code>
 * define("LOGGER_DEFAULT_LOG_FILE","/home/yarri/www/gr/sys/log/log");
 * </code>
 *
 * @package Atk14
 * @subpackage InternalLibraries
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
	 *
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
	 * @access private
	 */
	var $_disable_start_and_stop_marks = false;

	/**
	 * Events with $_no_log_level priority and lower are not sent to output.
	 *
	 * @access private
	 */
	var $_no_log_level;

	/**
	 * @access private
	 */
	var $_notify_level;

	/**
	 * @access private
	 */
	var $_notify_email;

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
	 * Value is set during {@link prepared_log("start")} call
	 *
	 * @access private
	 */
	var $_started_at_time = null;	

	/**
	 * @access private
	 */
	var $_levels = array(
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
	 * Constructor.
	 *
	 * @param string $prefix application_mark
	 * @param array $options
	 * <ul>
	 * <li>disable_start_and_stop_marks (false) - whether start and stop marks show up in output</li>
	 * <li>log_to_stdout (false) - if true output to stdout</li>
	 * </ul>
	 */
	function logger($prefix = "",$options = array()){
		$options = array_merge(array(
			"disable_start_and_stop_marks" => false,
			"log_to_stdout" => false,
			"default_log_file" => LOGGER_DEFAULT_LOG_FILE
		),$options);

		$this->_default_log_file = $options["default_log_file"];

		$this->_reset_configuration();
		$this->_my_pid = posix_getpid();

		$this->set_prefix($prefix);
		if($options["log_to_stdout"]){ $this->_log_file = "php://stdout"; }
		$this->_disable_start_and_stop_marks = $options["disable_start_and_stop_marks"];
	}

	/**
	 * Returns output filename.
	 *
	 * @return string
	 */
	function get_log_file(){ return $this->_log_file; }

	
	function get_no_log_level(){ return $this->_no_log_level; }
	function get_notify_level(){ return $this->_notify_level; }
	function get_notify_email(){ return $this->_notify_email; }

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

	/**
	 * @access private
	 */
	function _determin_configuration(){
		$this->_reset_configuration();
		for($i=0;$i<=strlen($this->_prefix);$i++){
			$this->_find_configuration(substr($this->_prefix,0,$i)."*");
		}
		$this->_find_configuration($this->_prefix);
	}

	/**
	 * @access private
	 */
	function _find_configuration($prefix){
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
	 * @access private
	 */
	function _reset_configuration(){
		$this->_no_log_level = -30;
		$this->_notify_level = 30;
		$this->_notify_email = "";
		$this->_log_file = $this->_default_log_file;
	}

	/**
	 * Returns specified log level as integer
	 *
	 * <code>
	 * $l_int = $logger->level_to_int("warn"); // returns 2
	 * $l_int = $logger->level_to_int(2); // returns 2
	 * $l_int = $logger->level_to_int(30); // (unknown level) returns 30
	 * </code>
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
	 * <code>
	 * $l_str = $logger->level_to_str("info"); // returns "info"
	 * $l_str = $logger->level_to_str(2); // returns "warn"
	 * $l_str = $logger->level_to_str("unknown"); // (unknown level) returns "unknown"
	 * </code>
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

		$fp = fopen($this->_log_file,"a");

		if(!$this->_notify_level_reached){
			reset($this->_log_store);
			while(list(,$rec) = each($this->_log_store)){
				if($rec['log_level']>=$this->_notify_level){
					$this->_notify_level_reached = true;
					break;
				}
			}
		}

		reset($this->_log_store);
		while(list(,$rec) = each($this->_log_store)){

			$this->_log_store_whole[] = $rec;

			if(!$this->_notify_level_reached && $rec['log_level']<=$this->_no_log_level){
				continue;
			}

			if(!is_bool(strpos($rec['log'],"\n"))){
				$_ar = explode("\n",$rec['log']);
				$rec['log'] = "";
				for($i=0;$i<sizeof($_ar);$i++){
					$rec['log']	.= "\n\t".$_ar[$i];
				}
			}
			
			$str = "[".$rec['date']." $this->_my_pid] ".$rec['prefix']."/".$rec['log_level']."=".$this->level_to_str($rec['log_level']).": ".$rec['log']."\n";
			fwrite($fp,$str,strlen($str));

		}

		fclose($fp);
		
		$this->_log_store = array();

		return 0;
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
			$this->_notify_email();
		}
		$this->_log_store_whole = array();
		return 0;
	}

	function flushAll(){ return $this->flush_all(); }

	/**
	 * Alias method for outputing message to specified level.
	 *
	 * @param string $log message to output
	 */
	function debug($log){ $this->put_log($log,-1); }

	/**
	 * Alias method for outputing message to specified level.
	 *
	 * @param string $log message to output
	 */
	function info($log){ $this->put_log($log,0); }
	
	/**
	 * Alias method for outputing message to specified level.
	 *
	 * @param string $log message to output
	 */
	function warn($log){ $this->put_log($log,2); }

	/**
	 * Alias method for outputing message to specified level.
	 *
	 * @param string $log message to output
	 */
	function error($log){ $this->put_log($log,4); }

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
		settype($log,"string");
		$log_level = $this->level_to_int($log_level);

		$this->_log_store[] = array(
			"date" => date("Y-m-d H:i:s"),
			"prefix" => $this->_prefix,
			"log_level" => $log_level,
			"log" => $log
		);

		return 0;
	}
	

	/**
	 * Starts logging.
	 *
	 * Prefix/application_mark can be defined
	 *
	 * @param string $prefix application_mark
	 */
	function start($prefix = ""){
		settype($prefix,"string");
		if(strlen($prefix)>0){ $this->set_prefix($prefix); }
		if(!$this->_disable_start_and_stop_marks){
			$this->prepared_log("start");
		}
	}

	/**
	 * Stops logging
	 *
	 * Also flushes all event to output
	 */
	function stop(){
		if(!$this->_disable_start_and_stop_marks){
			$this->prepared_log("stop");
		}
		$this->flush_all();
	}

	/**
	 * Method for starting and stopping logger.
	 *
	 * Preferred methods to call are {@link start()} and {@link stop()}
	 *
	 * @return int 0
	 */
	function prepared_log($style){
		settype($style,"string");
		switch(strtolower($style)){
			case "start":
				$this->put_log("START");
				$this->_started_at_time = $this->_get_microtime();
				if(!$this->_silent_mode){
					$str = "[".date("Y-m-d H:i:s")." $this->_my_pid] ".$this->_prefix."/0=info: START\n";
					echo $str;
				}
				break;
			case "stop":
				$_log = "STOP";
				if(isset($this->_started_at_time)){
					$_stopped = $this->_get_microtime();
					$_runing_time = $_stopped - $this->_started_at_time;
					$_minutes = (floor($_runing_time/60.0));
					$_log .= sprintf(", running time: %d min %0.2f sec",$_minutes,($_runing_time-($_minutes*60)));
				}
				$this->put_log($_log);
				if(!$this->_silent_mode){
					$str = "[".date("Y-m-d H:i:s")." $this->_my_pid] ".$this->_prefix."/0=info: STOP\n";
					echo $str;
				}
				break;
		}
		return 0;
	}

	/**
	 * @access private
	 */
	function _notify_email(){
		if($this->_notify_email==""){ return;}

		$output = "";
		$output .= "prefix: $this->_prefix\n";
		$output .= "pid: $this->_my_pid\n";
		$output .= "\n";
		
		reset($this->_log_store_whole);
		
		while(list(,$rec) = each($this->_log_store_whole)){
			$output .= "[".$rec['date']."] ".$rec['log_level']."=".$this->level_to_str($rec['log_level']).": ".$rec['log']."\n";
		}

		mail($this->_notify_email,"log report: $this->_prefix, ".date("Y-m-d H:i:s"),$output);
	}

	/**
	 * @access private
	 */
	function _get_microtime(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
	}
}

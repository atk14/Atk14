<?
/**
* Trida nabizi metody pro jednotne logovani udalosti.
*
*	// novy styl pouziti
* $logger = new logger("poznavaci_znacka_procesu");
* $logger->start();
*	// potom v aplikace
* $logger->info("a message");
* $logger->debug("a message");
* $logger->warn("a message");
* $logger->error("a message");
*	// ukonceni logovani
* $logger->stop();
*
* // v pripade, ze se necheme, aby se ve vystupu objevovaly znacky STAR a STOP, vytvorime instanci takto
* $logger = new logger("poznavaci_znacka_procesu",array("disable_start_and_stop_marks" => true));
*
*	// stary styl pouziti
* $logger = new logger();
* $logger->set_prefix("poznavaci_znacka_procesu");
* $logger->set_silent_mode(); //zapne tichy rezim - nic se nebude tisknout na stdout (nepouzije se echo)
*															//defaultne tiskne pri volani prepared_log("start") a prepared_log("stop") na stdout
* $logger->prepared_log("start");
* ...
* $logger->put_log("popis nejake cinnosti nebo chyby",0);
* ...
* $logger->prepared_log("stop");
* $logger->flush_all();
 
*	define("LOGGER_DEFAULT_LOG_FILE","/home/yarri/www/gr/sys/log/log");
*/

class logger{
	var $_prefix = "";
	var $_log_file;
	var $_log_store = array();
	var $_log_store_whole = array();
	var $_silent_mode = true;
	var $_disable_start_and_stop_marks = false;

	var $_no_log_level;
	var $_notify_level;
	var $_notify_email;
	var $_notify_level_reached = false;

	var $_my_pid;

	/**
	* Cas zahajeni logovani.
	* Bude nastaveno behem volani metody prepared_log("start")
	*/
	var $_started_at_time = null;	

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

	function logger($prefix = "",$options = array()){
		$options = array_merge(array(
			"disable_start_and_stop_marks" => false,
			"log_to_stdout" => false,
		),$options);

		$this->_reset_configuration();
		$this->_my_pid = posix_getpid();

		$this->set_prefix($prefix);
		if($options["log_to_stdout"]){ $this->_log_file = "php://stdout"; }
		$this->_disable_start_and_stop_marks = $options["disable_start_and_stop_marks"];
	}

	function get_log_file(){ return $this->_log_file; }
	function get_no_log_level(){ return $this->_no_log_level; }
	function get_notify_level(){ return $this->_notify_level; }
	function get_notify_email(){ return $this->_notify_email; }

	/**
	* Nastaveni prefixu.
	* Zde se rovnez nastavi atributy _notify_level, _notify_email, _no_log_level a _log_file
	*/
	function set_prefix($prefix){
		global $LOGGER_CONFIGURATION;
		settype($prefix,"string");
		$this->_prefix = $prefix;

		$this->_determin_configuration();
	}

	function _determin_configuration(){
		$this->_reset_configuration();
		for($i=0;$i<=strlen($this->_prefix);$i++){
			$this->_find_configuration(substr($this->_prefix,0,$i)."*");
		}
		$this->_find_configuration($this->_prefix);
	}

	/**
	* 
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
	* Resetuje konfuguraci na vychozi hodnoty.
	*/
	function _reset_configuration(){
		$this->_no_log_level = -30;
		$this->_notify_level = 30;
		$this->_notify_email = "";
		$this->_log_file = LOGGER_DEFAULT_LOG_FILE;
	}

	/**
	* $l_int = $logger->level_to_int("warn"); // vrati 2
	* $l_int = $logger->level_to_int(2); // vrati 2
	* $l_int = $logger->level_to_int(30); // (neznamy level) vrati 30
	*/
	function level_to_int($level){
		foreach($this->_levels as $key => $value){
			if(strcmp("$level",$key)==0 || strcmp("$level",$value)==0){ return (int)$key; }
		}
		return (int)$level;
	}

	/**
	* $l_str = $logger->level_to_str("info"); // vrati "info"
	* $l_str = $logger->level_to_str(2); // vrati "warn"
	* $l_str = $logger->level_to_str("unknown"); // (neznamy level) level "unknown"
	*/
	function level_to_str($level){
		foreach($this->_levels as $key => $value){
			if(strcmp("$level",$key)==0 || strcmp("$level",$value)==0){ return $value; }
		}
		return "$level";
	}


	/**
	* Nastavi silent mode, nebude se nic vypisovat na stdout.
	*
	* Volitelnym parametrem $mode, lze nastavit "hlasity" rezim.
	*	
	*	@access public
	* @param boolean $mode
	*	@return 0
	*/
	function set_silent_mode($mode = true){
		settype($mode,"boolean");
		$this->_silent_mode = $mode;
		return 0;
	}


	/******************************************
		@function flush

		@return 0

		@access public
	******************************************/
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

	function flush_all(){
		$this->flush();
		if($this->_notify_level_reached && $this->_notify_email!=""){
			$this->_notify_email();
		}
		$this->_log_store_whole = array();
		return 0;
	}

	function debug($log){ $this->put_log($log,-1); }
	function info($log){ $this->put_log($log,0); }
	function warn($log){ $this->put_log($log,2); }
	function error($log){ $this->put_log($log,4); }

	/**
	*	Zapise zpravu do logovaciho souboru.
  *
	*	Radeji pouzivejme $logger->error(), $logger->warn(), $logger->info() a $logger->debug().
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
	* Zacatek logovani.
	* Mozno definovat prefix.
	*/
	function start($prefix = ""){
		settype($prefix,"string");
		if(strlen($prefix)>0){ $this->set_prefix($prefix); }
		if(!$this->_disable_start_and_stop_marks){
			$this->prepared_log("start");
		}
	}

	/**
	* Ukonceni logovani.
	*/
	function stop(){
		if(!$this->_disable_start_and_stop_marks){
			$this->prepared_log("stop");
		}
		$this->flush_all();
	}

	/**
	* $logger->prepared_log("start");
	* $logger->prepared_log("stop");
	* 
	* lepe vsak volat:
	* $logger->start();
	* $logger->stop();
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

	function _get_microtime(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
	}
}
?>

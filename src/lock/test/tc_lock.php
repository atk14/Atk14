<?
class TcLock extends TcBase{
	/**
	* TODO: dodelat tento test.
	* Zatim to jenom tisken na STDOUT.
	*/
	function test_auto_kill(){
		$pid = pcntl_fork();

		if($pid == -1){
			$this->fail("could not fork");
		}elseif($pid){
			// jsme v parent
			$logger = $this->_new_logger();
			sleep(2);
			$logger->start();
			$logger->info("I'm parent");
			$logger->flush();
			// zde musi dojit k zabiti child
			define("LOCK_TIME_TO_KILL_INACTIVE_SCRIPTS",1);
			Lock::Mklock("test",$logger);
			Lock::Unlock("test",$logger);
			$logger->stop();
		}else{
			// jsme v child
			$logger = $this->_new_logger();
			$logger->start();
			$logger->info("I'm child");
			$logger->flush();
			Lock::Mklock("test",$logger);
			sleep(3);
			Lock::Unlock("test",$logger);
			$logger->stop();
		}
	}

	function _new_logger(){
		$logger = new logger("test",array(
			"log_to_stdout" => true
		));
		return $logger;
	}
}

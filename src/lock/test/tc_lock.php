<?php
class TcLock extends TcBase{

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
			$this->assertTrue(true); // tady se dostaneme
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
			$this->assertTrue(false); // sem se dostat nesmime
		}
	}

	function _new_logger(){
		$logger = new Logger("test",array(
			"log_to_stdout" => true
		));
		return $logger;
	}
}

<?php
/**
* Trida nabizi staticke metody Lock::Mklock() a Lock::Unlock() pro zbezpecne zamknuti procesu.
* Pouziva se pro importni procesy z duvodu, aby v jednu chvili bezel vzdy jen jednou proces s danym jmenem zamku.
* Realne vytvari soubor v zamykacim adresari - musi byt definovan LOCK_DIR!!!
* Pro logovani se pouziv objekt tridy logger.
*
* TODO: Jedna se o velmi starou tridu, kterou by bylo dobre pofackovat
*/
class Lock{
	
	/**
	*	Zamkne proces podle jmena.
	* POZOR!!! Pokud zamek existuje, bude zavolan exit().
	*
	* @static
	* @access public
	*	@param string $lock_name														jmeno procesu
	*	@param logger &$logger
	*	@param integer $time_to_kill_inactive_scripts
	*	@param integer kill_signal
	*	@return integer 																		vzdy 0
	*/
	static function Mklock($lock_name,&$logger,$time_to_kill_inactive_scripts = null,$kill_signal = 9){
		settype($lock_name,"string");
		if(isset($time_to_kill_inactive_scripts)){
			settype($time_to_kill_inactive_scripts,"integer");
		}elseif(defined("LOCK_TIME_TO_KILL_INACTIVE_SCRIPTS")){
			$time_to_kill_inactive_scripts = LOCK_TIME_TO_KILL_INACTIVE_SCRIPTS;
			settype($time_to_kill_inactive_scripts,"integer");
		}
		settype($kill_signal,"integer");

		if(!file_exists(LOCK_DIR)){
			mkdir(LOCK_DIR);
		}

		$lock_name = Lock::_ValidLockName($lock_name);

		$lock_path = LOCK_DIR;
		ignore_user_abort(3);
		$my_pid = posix_getpid();
		$lock_file = "$lock_path/$lock_name";
		$output = "";
		if(file_exists($lock_file)){
			$output .= "file $lock_file exists!\n";
			$f = fopen($lock_file,"r");
			$stat = fread($f,1024);
			$stat_pid = false;
			if(ereg('^([0-9]{1,}) ([0-9]{1,})$',$stat,$pieces)){
				$stat = $pieces[1];
				$stat_pid = $pieces[2];
				settype($stat_pid,"integer");
			}
			if(is_int($stat_pid) && Lock::_IsProcDirMounted()){
				//KONTROLA /proc/PID mozna nekdy nestaci - pokud se sposuti import apachem, proces muze existovat, ale spi.. :(
				$stat_process_chcek_file = "/proc/$stat_pid";
				if(file_exists($stat_process_chcek_file) && is_dir($stat_process_chcek_file)){
					//cas zabit?
					if(is_int($time_to_kill_inactive_scripts) && ($stat+$time_to_kill_inactive_scripts)<time()){
						$kill_status = posix_kill($stat_pid,$kill_signal);
						if($kill_status){
							$stat_date = date("Y-m-d H:i:s",$stat);
							$output .= "(current time: ".date("Y-m-d H:i:s").", my pid is $my_pid)\n";
							$output .= "another script was running (PID $stat_pid), started at $stat_date\n";
							$output .= "but was killed ($time_to_kill_inactive_scripts)\n";
						}else{
							$stat_date = date("Y-m-d H:i:s",$stat);
							$output .= "(current time: ".date("Y-m-d H:i:s").", my pid is $my_pid)\n";
							$output .= "another script is runing (PID $stat_pid), started at $stat_date\n";
							$output .= "if this is not true, remove lock file $lock_file\n";
							$output .= "additionaly: I tried to kill running process. but kill wasn't successfull\n";
							$output .= "(exiting...)\n";
							$logger->info($output);
							$logger->flush_all();
							unset($logger);
							exit;
						}
					}else{
						$stat_date = date("Y-m-d H:i:s",$stat);
						$output .= "(current time: ".date("Y-m-d H:i:s").", my pid is $my_pid)\n";
						$output .= "another script is runing (PID $stat_pid), started at $stat_date\n";
						$output .= "if this is not true, remove lock file $lock_file\n";
						$output .= "(exiting...)\n";
						$logger->info($output);
						$logger->flush_all();
						unset($logger);
						exit;
					}
				}else{
					$output .= "lock: lock file $lock_file exists, but /proc/$stat_pid doesn't! removing the lock file\n";
					unlink ($lock_file);
				}
			}else{
				$stat_date = date("Y-m-d H:i:s",$stat);
				$output .= "(current time: ".date("Y-m-d H:i:s").", my pid is $my_pid)\n";
				$output .= "another script is runing, started at $stat_date\n";
				$output .= "if this is not true, remove lock file $lock_file\n";
				$output .= "(exiting...)\n";
				$logger->info($output);
				$logger->flush_all();
				unset($logger);
				exit; 
			}
		}
		$f = fopen($lock_file,"w");
		$_string_to_write = time()." ".$my_pid;
		$_bytes_written = fwrite($f,$_string_to_write,strlen($_string_to_write));
		fclose($f);

		if(strlen($_string_to_write)!=$_bytes_written){
			$stat_date = date("Y-m-d H:i:s",$stat);
			$output .= "(current time: ".date("Y-m-d H:i:s").", my pid is $my_pid)\n";
			$output .= "the writing to the lock file failed\n";
			$output .= "the lock file is $lock_file\n";
			$output .= "removing the lock file\n";
			$output .= "(exiting...)\n";
			$logger->info($output);
			$logger->flush_all();
			unset($logger);
			unlink($lock_file);
			exit; 
		}

		if(strlen($output)>0){
			$logger->info($output);
		}

		return 0;
	}


	/**
	* Uvolni zamek procesu daneho jmena.
	*	
	* @static
	* @access public
	*	@param string lock_name
	*	@param logger &$logger
	*	@return integer									vzdy 0
	*/
	static function Unlock($lock_name = "",&$logger){
		$lock_name = Lock::_ValidLockName($lock_name);
		$lock_path = LOCK_DIR;
		$lock_file = "$lock_path/$lock_name";
		unlink($lock_file);
		return 0;
	}

	/**
	* Zjisti, zda je pripojen adresar /proc/.
	*	Prakticky se hleda v tomto adresari prvni podadresar, ktery ma ve jmene pouze cislice.
	* Pak je rozhodnuto, ze /proc je pripojen.
	* 
	*	@static 
	* @access private
	* @return boolean
	*/
	function _IsProcDirMounted(){
		$out = false;
		if(file_exists("/proc") && is_dir("/proc")){
			$dir_handle = opendir("/proc/");
			while($filename = readdir($dir_handle)){
				if(preg_match("/^[0-9]{1,}$/",$filename) && is_dir("/proc/$filename")){
					$out = true;
					break;
				}
			}
			closedir($dir_handle);
		}
		return $out;
	}

	function _ValidLockName($lock_name){
		$lock_name = preg_replace('/[^a-zA-Z0-9_.-]/','',$lock_name);
		if($lock_name==""){$lock_name = "default_lock";}
		return $lock_name;
	}
}

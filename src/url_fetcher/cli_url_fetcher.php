<?php
class CliUrlFetcher extends UrlFetcher {

	protected $cli_command;

	function __construct($cli_command,$url = "",$options = array()){
		$this->cli_command = $cli_command;
		parent::__construct($url,$options);
	}

	protected function _makeWritingAndReading(){
		$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
			1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			2 => array("pipe", "w") // stderr is a pipe that the child will write to
		);

		$cwd = getcwd();
		$env = array();

		$process = proc_open($this->cli_command, $descriptorspec, $pipes, $cwd, $env);

		if(!is_resource($process)){
			return $this->_setError("cannot do proc_open");
		}

		$_buffer = new StringBuffer($this->_RequestHeaders);
		if($this->_RequestMethod=="POST"){
			$_buffer->addStringBuffer($this->_PostData);
		}
		$stat = $this->_fwriteStream($pipes[0],$_buffer);
		fclose($pipes[0]);

		// stderr
		$err = fread($pipes[2],1024 * 256);
		fclose($pipes[2]);
		
		if(!$stat || $stat!=$_buffer->getLength()){
			return $this->_setError(sprintf("cannot write to proc (bytes written: %s, bytes needed to be written: %s)",$stat,$_buffer->getLength()));
		}

		$headers = "";
		$_buffer = new StringBufferTemporary();
		$f = $pipes[1];
		while(!feof($f) && $f){
			$_b = fread($f,1024 * 256); // 256kB
			if(strlen($_b)==0){
				usleep(20000);
				continue;
			}
			$_buffer->addString($_b);

			if(!strlen($headers) && preg_match("/^(.*?)\\r?\\n\\r?\\n(.*)$/s",$_buffer->toString(),$matches)){
				$headers = $matches[1];
				$_b = $matches[2];
				$_buffer = new StringBufferTemporary();
				(strlen($_b)>0) && ($_buffer->addString($_b));
			}
		}
		fclose($f);

		if(!$_buffer->getLength() && !strlen($headers)){ // content ($_buffer) may be empty
			return $this->_setError("failed to read from proc");
		}

		if(!strlen($headers)){
			return $this->_setError("can't find response headers");
		}

		return array($headers,$_buffer);
	}
}

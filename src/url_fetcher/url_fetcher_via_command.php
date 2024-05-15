<?php
class UrlFetcherViaCommand extends UrlFetcher {

	protected $command;

	function __construct($command,$url = "",$options = array()){
		$this->command = $command;
		parent::__construct($url,$options);
	}

	protected function _makeWritingAndReading(){
		$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
			1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			2 => array("file", "/dev/null", "a")
			//2 => array("pipe", "w") // stderr is a pipe that the child will write to
		);

		$cwd = getcwd();
		$env = array();

		$process = proc_open($this->command, $descriptorspec, $pipes, $cwd, $env);

		if(!is_resource($process)){
			return $this->_setError("cannot do proc_open");
		}

		$_buffer = new StringBuffer($this->_RequestHeaders);
		if($this->_RequestMethod=="POST"){
			$_buffer->addStringBuffer($this->_BodyData);
		}
		$stat = $this->_fwriteStream($pipes[0],$_buffer);

		// stderr
		//$err = fread($pipes[2],1024 * 256);
		
		if(!$stat || $stat!=$_buffer->getLength()){
			return $this->_setError(sprintf("cannot write to proc (bytes written: %s, bytes needed to be written: %s)",$stat,$_buffer->getLength()));
		}

		$headers = "";
		$_buffer = new StringBufferTemporary();
		$content_length = null;

		$f = $pipes[1];

		stream_set_blocking($f,0);
		while(!feof($f) && $f){
			if(isset($content_length) && $_buffer->getLength()>=$content_length){
				break;
			}
			$_b = fread($f,1024 * 60); // 60kB
			if(strlen($_b)==0){
				// TODO: exit cycle after $this->_SocketTimeout
				usleep(1000); // 1ms
				continue;
			}
			$_buffer->addString($_b);

			if(!strlen($headers) && preg_match("/^(.*?)\\r?\\n\\r?\\n(.*)$/s",$_buffer->toString(),$matches)){
				$headers = $matches[1];
				$_b = $matches[2];
				$_buffer = new StringBufferTemporary();
				(strlen($_b)>0) && ($_buffer->addString($_b));
				if(preg_match('/Content-Length: ([1-9]\d+)/i',$headers,$matches)){
					$content_length = $matches[1];
				}
			}
		}

		fclose($pipes[0]);
		fclose($pipes[1]);
		//fclose($pipes[2]);

		$return_value = proc_close($process);

		if($return_value!==0){
			return $this->_setError("command '$this->command' returned error code $return_value");
		}

		if(!$_buffer->getLength() && !strlen($headers)){ // content ($_buffer) may be empty
			return $this->_setError("failed to read from proc");
		}

		if(!strlen($headers)){
			return $this->_setError("can't find response headers");
		}

		return array($headers,$_buffer);
	}
}

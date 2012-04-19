<?php
class TcBase extends TcSuperBase{
	function setUp(){
		$this->_remove_log_files();
	}

	function _remove_log_files(){
		foreach(array(
			"default.log",
			"cache_remover.log",
			"import.log",
			"another.log",
			"special.log"
		) as $f){
			$filename = __DIR__."/log/$f";
			if(file_exists($filename)){
				unlink($filename);
			}
		}
	}
}

<?php
if(PHP_MAJOR_VERSION<7){

	$src = file_get_contents(__DIR__ . "/tc_string4.php");
	$src = str_replace("String4","String",$src);
	$src = preg_replace('/^<\?php\s*/','',$src);
	eval($src);

}else{

	class TcString extends TcBase{
		function test(){
			$this->assertTrue(true);
		}
	}

}


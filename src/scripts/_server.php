<?php
if(preg_match('/^\/public\//',$_SERVER["REQUEST_URI"])){
	return false;
}

if(preg_match('/^\/(favicon.ico|crossdomain.xml)(|\?.*)/',$_SERVER["REQUEST_URI"],$matches)){
	switch($matches[1]){
		case "favicon.ico":
			$c_type = "image/x-icon";
			break;
		case "crossdomain.xml":
			$c_type = "text/xml";
			break;
	}
	header("Content-Type: $c_type");
	readfile("public/$matches[1]");
	exit;
}

require("dispatcher.php");

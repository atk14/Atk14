<?php

// "/public/dist/styles/application.min.v1507035144.css" -> "/public/dist/styles/application.min.css"
if(preg_match('/^\/public\/([^?]+)\.v[0-9a-f]{1,64}\.([a-zA-Z0-9]{1,10})(|\?.*)$/',$_SERVER["REQUEST_URI"],$matches) && file_exists("public/$matches[1].$matches[2]")){
	$mime_types = [
		"css" => "text/css",
		"js" => "text/javascript"
	];
	$suffix = strtolower($matches[2]);
	if(isset($mime_types[$suffix])){
		header("Content-Type: ".$mime_types[$suffix]);
	}
	readfile("public/$matches[1].$matches[2]");
	exit;
}

if(preg_match('/^\/public\//',$_SERVER["REQUEST_URI"])){
	return false;
}

// "/favicon.ico" -> "/public/favicon.ico"
if(preg_match('/^\/(favicon.ico|crossdomain.xml)(|\?.*)$/',$_SERVER["REQUEST_URI"],$matches)){
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

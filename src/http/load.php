<?php
require_once(__DIR__ . "/src/_httputils.php");
require_once(__DIR__ . "/src/httpcookie.php");
require_once(__DIR__ . "/src/httprequest.php");
require_once(__DIR__ . "/src/httpresponse.php");
require_once(__DIR__ . "/src/httpuploadedfile.php");
require_once(__DIR__ . "/src/httpxfile.php");

if(!isset($HTTP_RESPONSE)){
	global $HTTP_RESPONSE, $HTTP_REQUEST, $HTTP_OUTPUT_BUFFER;
	$HTTP_RESPONSE = new HTTPResponse();
	$HTTP_REQUEST = new HTTPRequest();
	$HTTP_OUTPUT_BUFFER = $HTTP_RESPONSE->getOutputBuffer();
}

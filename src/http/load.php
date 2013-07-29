<?php
$__PATH__ = dirname(__FILE__);
require_once("$__PATH__/httpcookie.php");
require_once("$__PATH__/httprequest.php");
require_once("$__PATH__/httpresponse.php");
require_once("$__PATH__/httpuploadedfile.php");
require_once("$__PATH__/httpxfile.php");

if(!isset($HTTP_RESPONSE)){
	$HTTP_RESPONSE = new HTTPResponse();
	$HTTP_REQUEST = new HTTPRequest();
	$HTTP_OUTPUT_BUFFER = &$HTTP_RESPONSE->getOutputBuffer();
}

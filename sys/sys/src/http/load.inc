<?
$__PATH__ = dirname(__FILE__);
require_once("$__PATH__/httpcookie.inc");
require_once("$__PATH__/httprequest.inc");
require_once("$__PATH__/httpresponse.inc");
require_once("$__PATH__/httpuploadedfile.inc");

if(!isset($HTTP_RESPONSE)){
	$HTTP_RESPONSE = new HTTPResponse();
	$HTTP_REQUEST = new HTTPRequest();
	$HTTP_OUTPUT_BUFFER = &$HTTP_RESPONSE->getOutputBuffer();
}
?>

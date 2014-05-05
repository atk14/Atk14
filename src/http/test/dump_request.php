<?php
/**
 * Dumps the HTTP request into a YAML.
 */

require_once(dirname(__FILE__)."/../../stringbuffer/load.php");
require_once(dirname(__FILE__)."/../../miniyaml/load.php");
require_once(dirname(__FILE__)."/../load.php");

$request = &$HTTP_REQUEST;
$response = &$HTTP_RESPONSE;

$result = array(
	"content-type" => $request->getContentType(),
	"content-charset" => $request->getContentCharset(),
);

$response->setContentType("text/plain");
$response->write(miniYAML::Dump($result));
$response->flushAll();

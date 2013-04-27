<?php
require(dirname(__FILE__)."/initialize.inc");

$ser = Files::GetFileContent("response.ser",$err,$err_msg);
$response = unserialize($ser);

$response->flushAll();

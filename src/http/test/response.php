<?
require(dirname(__FILE__)."/initialize.inc");

$ser = files::get_file_content("response.ser",$err,$err_msg);
$response = unserialize($ser);

$response->flushAll();

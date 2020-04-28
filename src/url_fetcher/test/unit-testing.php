<?php
header('Content-Type: text/plain; charset="UTF-8"');

echo "Request method is $_SERVER[REQUEST_METHOD].\n";

if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on"){
	echo "You are on the ssl.\n";
}else{
	echo "You are not on the ssl.\n";
}

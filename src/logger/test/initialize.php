<?php
define("LOGGER_DEFAULT_LOG_FILE",__DIR__."/log/default.log");
require("../logger.php");
require("../../files/load.php");
$LOGGER_CONFIGURATION = array(
	"cache_remover" => array(
		"log_file" => __DIR__."/log/cache_remover.log",
	),
	"import_*" => array(
		"log_file" => __DIR__."/log/import.log",
	),
);

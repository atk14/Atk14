<?php
define("TEST",true);
define("LOGGER_DEFAULT_LOG_FILE",__DIR__."/log/default.log");
define("SENDMAIL_DO_NOT_SEND_MAILS",true);

require("../logger.php");
require("../../files/load.php");
require("../../sendmail/load.php");
require("../../translate/load.php");

$LOGGER_CONFIGURATION = array(
	"cache_remover" => array(
		"log_file" => __DIR__."/log/cache_remover.log",
	),
	"import_*" => array(
		"notify_email" => "import.notification@doe.com",
		"log_file" => __DIR__."/log/import.log",
	),
);

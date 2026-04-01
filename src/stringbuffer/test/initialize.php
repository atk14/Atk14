<?php
define("TEST",true);
define("TEMP",__DIR__ . "/tmp/");
require_once("../load.php");
require_once("../../files/load.php");

StringBufferTemporary::$FILEIZE_THRESHOLD = 5; // 5 bytes

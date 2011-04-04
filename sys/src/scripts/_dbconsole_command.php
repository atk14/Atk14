#!/usr/bin/env php
<?php

require_once(dirname(__FILE__)."/load.inc");

$d = $ATK14_GLOBAL->getDatabaseConfig();

$cmd = "psql -U $d[username] $d[database] -h $d[host]";

echo $cmd;
echo "\n";

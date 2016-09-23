<?php
/**
 * echo dump_db_command("all");
 * echo dump_db_command("schema");
 * echo dump_db_command("data");
 */
function dump_db_command($dump = "all"){
	global $argv,$ATK14_GLOBAL;

	$d = $ATK14_GLOBAL->getDatabaseConfig();

	$arguments = $argv;
	array_shift($arguments); // removing script name

	switch($d["adapter"]){
		case "postgresql":
			return pg_dump_command($d,$arguments,$dump);
		case "mysql":
			return mysql_dump_command($d,$arguments,$dump);
	}	
}

/**
 * $cmd = pg_dump_command($database_config,$argv)
 */
function pg_dump_command($d,$arguments = array(),$dump){
	$dump_options = "";
	if($dump=="schema"){ $dump_options = " --schema-only"; }
	elseif($dump=="data"){ $dump_options = " --data-only"; }
	if($d["port"]){ array_unshift($arguments,"-p $d[port]"); }
	if($d["host"]){ array_unshift($arguments,"-h $d[host]"); }
	$cmd = "pg_dump$dump_options -U $d[username] $d[database]".($arguments ? " ".join(" ",$arguments) : "");
	return $cmd;
}

function mysql_dump_command($d,$arguments = array(),$dump){
	$dump_options = "";
	if($dump=="schema"){ $dump_options = " --no-data"; }
	elseif($dump=="data"){ $dump_options = " --no-create-info"; }
	$cmd = "mysqldump$dump_options -u$d[username] -h$d[host] -P$d[port] -p$d[password] $d[database]".($arguments ? " ".join(" ",$arguments) : "");
	return $cmd;
}

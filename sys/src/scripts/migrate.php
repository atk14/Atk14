#!/usr/bin/env php
<?
require_once(dirname(__FILE__)."/load.inc");

$logger = &Atk14Migration::GetLogger();

// getting list of migration files
$migrations = array();
$dir = opendir($ATK14_GLOBAL->getMigrationsPath());
while($item = readdir($dir)){
	if(preg_match("/(.+)\\.sql$/",$item,$matches)){
		$migrations[] = $item;
	}
}
closedir($dir);
asort($migrations);

// getting list of migration done migrations
$already_done_migrations = $dbmole->selectIntoArray("SELECT version FROM schema_magrations ORDER BY version");

$counter = 0;
foreach($migrations as $m){
	if(in_array($m,$already_done_migrations)){ continue; }

	$logger->info("about to start migration $m"); $logger->flush();

	$migr = new Atk14MigrationBySqlFile($m);
	if(!$migr->migrateUp()){
		break; // an error occured
	}

	$logger->info("migration $m has been successfully finished"); $logger->flush();

	$counter++;
}

if($counter==0){ $logger->info("there is nothing to migrate"); }

$logger->flush_all();

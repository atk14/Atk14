#!/usr/bin/env php
<?php
/**
* Initializes database! :)
* Creates all necessaries in there.
*
* At the moment only the table schema_migrations will be created.
*
* Usage:
*  $ ATK14_ENV=DEVELOPMENT ./scripts/initialize_database.php
*  $ ATK14_ENV=TEST ./scripts/initialize_database.php
*/

require_once(dirname(__FILE__)."/load.inc");

$dbmole->doQuery("CREATE TABLE schema_migrations(version VARCHAR(255) PRIMARY KEY);");


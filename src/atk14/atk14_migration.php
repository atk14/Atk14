<?php
/**
 * Class for managing database migrations.
 *
 * @filesource
 */

/**
 * Class for managing database migrations.
 *
 * Common migration scripts are written in PHP.
 * If you want to use an SQL in a migration step look at the {@link Atk14MigrationBySqlFile} class.
 *
 * Example of migration file(db/migrations/0001_content_for_creatures.php):
 * ```
 * class ContentForCreatures extends Atk14Migration{
 * 	function up(){
 * 		$data_ar = array(
 * 			array(
 * 				"name" => "Second creature",
 * 				"description" => "Normal creature. No picture is needed."
 * 			),
 * 			array(
 * 				"name" => "Third creature",
 * 				"description" => "Yet another creature."
 * 			)
 * 		);
 * 		foreach($data_ar as $data){
 * 			Creature::CreateNewRecord($data);
 * 		}
 * 	}
 * }
 * ```
 *
 * @package Atk14\Core
 */
class Atk14Migration{

	/**
	 * Constructor.
	 *
	 * @param string $version Migration file
	 */
	function Atk14Migration($version){
		$this->version = $version;
		$this->dbmole = &$GLOBALS["dbmole"];
		$this->_failed = false;

		$this->logger = &Atk14Migration::GetLogger();
	}

	/**
	 * Gets logger instance
	 *
	 * @return logger
	 */
	static function &GetLogger(){
		static $logger;
		if(!isset($logger)){
			$logger = new logger("migration",array(
				"log_to_stdout" => true,
			));
		}
		return $logger;
	}

	/**
	 * Process prepared migrations.
	 */
	function migrateUp(){
		$this->dbmole->begin();
		$this->up();
		if($this->_failed){ return; }
		$this->dbmole->commit();

		$this->dbmole->begin();
		// when we are forcing some migration, the given record in schema_migrations already exists
		if(0==$this->dbmole->selectInt("SELECT COUNT(*) FROM schema_migrations WHERE version=:version",array(":version" => $this->version))){
			$this->dbmole->insertIntoTable("schema_migrations",array("version" => $this->version));
		}
		$this->dbmole->commit();

		return true;
	}

	/**
	 * Abstract method to be overridden in subclass to execute the migration.
	 *
	 * @abstract
	 */
	function up(){
		// must be covered by the descendent...
		throw new Exception(sprintf("method %s::up() has to be defined",get_class($this)));
	}

	// TODO: to be implemented: migrateDown() and down(), unless it is not needed

	/**
	 * @ignore
	 * @access private
	 */
	function _fail($message){
		$this->logger->error($message);
		$this->logger->flush();
		$this->_failed = true;
	}

	/**
	 * Checks if table schema_migrations exists.
	 *
	 * @param DbMole $dbmole
	 * @return boolean
	 */
	static function SchemaMigrationsTableExists($dbmole){
		$bind_ar = array();
		switch($dbmole->getDatabaseType()){
			case "postgresql":
				$query = "SELECT COUNT(*) FROM pg_tables WHERE LOWER(tablename)='schema_migrations' AND schemaname=:schema";
				$bind_ar[":schema"] = self::GetDatabaseSchema($dbmole);
				break;
			case "mysql":
				$query = "SELECT COUNT(*) FROM information_schema.tables WHERE LOWER(table_name)='schema_migrations' LIMIT 1";
				break;
		}
		return 1==$dbmole->selectInt($query,$bind_ar);
	}

	/**
	 * Creates table for storing information about executed migrations.
	 *
	 * @param DbMole $dbmole
	 */
	static function CreateSchemaMigrationsTable($dbmole){
		// it's ok for postgresql and mysql
		$dbmole->doQuery("CREATE TABLE schema_migrations(
			version VARCHAR(255) PRIMARY KEY,
			created_at TIMESTAMP NOT NULL DEFAULT NOW()
		)");
	}

	/**
	 * Detects current database schema
	 *
	 *	echo Atk14Migration::GetDatabaseSchema($dbmole); // e.g. "public"
	 */
	static function GetDatabaseSchema($dbmole = null){
		if(!$dbmole){ $dbmole = $GLOBALS["dbmole"]; }
		switch($dbmole->getDatabaseType()){
			case "postgresql":
				$search_path = $dbmole->selectSingleValue("SHOW search_path"); // '"$user",public'
				$search_path = preg_replace('/\s+/','',$search_path); // '"$user", public' -> '"$user",public'
				$schemas = explode(",",$search_path);
				$schemas = array_diff($schemas,array('"$user"'));
				$schemas = array_values($schemas);
				if($schemas){ return $schemas[0]; }
				break;
			case "mysql":
				return $dbmole->selectSingleValue("SELECT DATABASE()");
				break;
		}
	}

	/**
	 * Sets the default database schema
	 *
	 *	Atk14Migration::SetDatabaseSchema("application");
	 *	Atk14Migration::SetDatabaseSchema("application",$dbmole);
	 */
	static function SetDatabaseSchema($schema,$dbmole = null){
		if(!$dbmole){ $dbmole = $GLOBALS["dbmole"]; }
		switch($dbmole->getDatabaseType()){
			case "postgresql":
				if(!preg_match('/^[a-z][a-z0-9_]{0,62}$/i',$schema)){
					throw new Exception("Invalid schema name");
				}
				$dbmole->doQuery("SET search_path TO $schema");
				TableRecord_DatabaseAccessor_Postgresql::SetDefaultDatabaseSchema($schema);
				break;
			case "mysql":
				throw new Exception("There are no schemas in mysql"); // TODO: really? :)
				break;
		}
	}
}

/**
 * Allows using sql in migration scripts.
 *
 * ```
 * $migration = Atk14MigrationBySqlScript("0000_sessions.sql");
 * $migration->migrateUp();
 * ```
 *
 * @package Atk14
 * @subpackage Core
 */
class Atk14MigrationBySqlFile extends Atk14Migration{
	/**
	 * Executes migration script containing plain sql.
	 */
	function up(){
		global $ATK14_GLOBAL;
		$filename = $ATK14_GLOBAL->getMigrationsPath().$this->version;

		$content = Files::GetFileContent($filename,$err,$err_str);

		if($err){
			return $this->_fail("can't read $filename: $err_str");
		}
		
		if($this->dbmole->getDatabaseType()=='oracle'){

			// This is sick.
			// Oracle is unable to execute script with several sql commands at once.
			// So... look at the very provisional workaround.

			foreach(explode(";",$content) as $q){
				$q = trim($q); if(!$q){ continue; }
				$this->dbmole->doQuery($q);
			}
		}elseif($this->dbmole->getDatabaseType()=='mysql'){
			$this->dbmole->multiQuery($content);
		}else{
			$this->dbmole->doQuery($content);
		}
	}
}

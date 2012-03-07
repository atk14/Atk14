<?php
/**
 * Class for managing database migrations.
 *
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Class for managing database migrations.
 *
 * Common migration scripts are written in PHP.
 * If you want to use an SQL in a migration step look at the {@link Atk14MigrationBySqlFile} class.
 *
 * Example of migration file(db/migrations/0001_content_for_creatures.php):
 * <code>
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
 * </code>
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 */
class Atk14Migration{

	function Atk14Migration($version){
		$this->version = $version;
		$this->dbmole = &$GLOBALS["dbmole"];
		$this->_failed = false;

		$this->logger = &Atk14Migration::GetLogger();
	}

	/**
	 * @static
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
		$this->dbmole->insertIntoTable("schema_migrations",array("version" => $this->version));
		$this->dbmole->commit();

		return true;
	}

	/**
	 * @abstract
	 */
	function up(){
		// must be covered by the descendent...
	}

	// TODO: to be implemented: migrateDown() and down(), unless it is not needed

	/**
	 * @access private
	 */
	function _fail($message){
		$this->logger->error($message);
		$this->logger->flush();
		$this->_failed = true;
	}
}

/**
 * Allows using sql in migration scripts.
 *
 * <code>
 * $migration = Atk14MigrationBySqlScript("0000_sessions.sql");
 * $migration->migrateUp();
 * </code>
 *
 * @package Atk14
 * @subpackage Core
 */
class Atk14MigrationBySqlFile extends Atk14Migration{
	function up(){
		global $ATK14_GLOBAL;
		$filename = $ATK14_GLOBAL->getMigrationsPath().$this->version;

		$content = files::get_file_content($filename,$err,$err_str);

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

		}else{
			$this->dbmole->doQuery($content);
		}
	}
}

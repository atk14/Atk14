<?
/**
 * Class for managing database migrations.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 */

/**
 * Class for managing database migrations.
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
	function &GetLogger(){
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
		$this->dbmole->insertIntoTable("schema_magrations",array("version" => $this->version));
		$this->dbmole->commit();

		return true;
	}

	/**
	 * @abstract
	 */
	function up(){
		// must be coverred by the descendent...
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
* $migration = Atk14MigrationBySqlScript("0000_sessions.sql");
* $migration->migrateUp();
*/
class Atk14MigrationBySqlFile extends Atk14Migration{
	function up(){
		global $ATK14_GLOBAL;
		$filename = $ATK14_GLOBAL->getMigrationsPath().$this->version;

		$content = files::get_file_content($filename,$err,$err_str);

		if($err){
			return $this->_fail("can't read $filename: $err_str");
		}
		
		$this->dbmole->doQuery($content);
	}
}

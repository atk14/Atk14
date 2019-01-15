<?php
class TableRecord_DatabaseAccessor_Mysql implements iTableRecord_DatabaseAccessor {

	/**
	 * Reads (physically) table structure from the database
	 *
	 * @ignore
	 */
	static function ReadTableStructure($record,$options = array()){
		$dbmole = $record->dbmole;

		$rows = $dbmole->selectRows(sprintf("DESCRIBE %s",$dbmole->escapeTableName4Sql($record->getTableName())));

		$out = array();
		foreach($rows as $row){
			$out[$row["Field"]] = $row["Type"];
		}

		return $out;
	}

	/**
	 * Converts database type into into the corresponding internal type
	 *
	 * @ignore
	 */
	static function DatabaseTypeToInternalType($database_type){
		if(preg_match("/^(decimal|float)/",$database_type)){
			return "float";
		}

		if(preg_match("/^(int|bigint|tinyint)/",$database_type)){
			return "integer";
		}

		if(preg_match("/^timestamp/",$database_type)){
			return "timestamp";
		}

		if(preg_match("/^bool/",$database_type)){
			return "boolean";
		}

		return "string";
	}
}

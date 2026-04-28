<?php
class TableRecord_DatabaseAccessor_Sqlsrv implements iTableRecord_DatabaseAccessor {

	/**
	 * Reads (physically) table structure from the database
	 *
	 * @ignore
	 */
	static function ReadTableStructure($record,$options = array()){
		$dbmole = $record->dbmole;

		$q = "
			SELECT
				COLUMN_NAME, DATA_TYPE
			FROM
				INFORMATION_SCHEMA.COLUMNS
			WHERE
				TABLE_NAME = '%s'";
		$rows = $dbmole->selectRows(sprintf($q, $dbmole->escapeTableName4Sql($record->getTableName())));

		$out = array();
		foreach($rows as $row){
			$out[$row["COLUMN_NAME"]] = $row["DATA_TYPE"];
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

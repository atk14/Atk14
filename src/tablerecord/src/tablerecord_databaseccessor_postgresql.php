<?php
class TableRecord_DatabaseAccessor_Postgresql implements iTableRecord_DatabaseAccessor {

	protected static $_DefaultDatabaseSchema = "public";

	/**
	 * Reads (physically) table structure from the database
	 *
	 * @ignore
	 */
	static function ReadTableStructure($record,$options = array()){
		$schema = self::GetDefaultDatabaseSchema(); // e.g. "public"
		$tblNameAr = explode(".", "$schema.".$record->getTableName());

		$_table = array_pop($tblNameAr);
		$_schema = array_pop($tblNameAr);

		$query = "
			SELECT
				a.attname,
				format_type(a.atttypid, a.atttypmod) AS format
			FROM
				pg_catalog.pg_class c INNER JOIN
				pg_catalog.pg_namespace n ON (c.relnamespace = n.oid) INNER JOIN
				pg_catalog.pg_attribute a ON (a.attrelid = c.oid)
			WHERE
				n.nspname = :schema_name AND
				c.relname = :table_name AND
				a.attisdropped = false AND
				a.attnum > 0
		";
		return $record->dbmole->selectIntoAssociativeArray($query,array(":table_name" => $_table, ":schema_name" => $_schema),$options);
	}

	/**
	 * Converts database type into into the corresponding internal type
	 *
	 * @ignore
	 */
	static function DatabaseTypeToInternalType($database_type){
		if(preg_match("/^(numeric|double precision)/",$database_type)){
			return "float";
		}

		if(preg_match("/^(integer|bigint|smallint)/",$database_type)){
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

	/**
	 *
	 *	echo TableRecord_DatabaseAccessor_Postgresql::GetDefaultDatabaseSchema(); // e.g. "public"
	 */
	static function GetDefaultDatabaseSchema(){
		return self::$_DefaultDatabaseSchema;
	}

	/**
	 *
	 *	TableRecord_DatabaseAccessor_Postgresql::SetDefaultDatabaseSchema("application");
	 */
	static function SetDefaultDatabaseSchema($namespace){
		self::$_DefaultDatabaseSchema = $namespace;
	}
}

<?php
interface iTableRecord_DatabaseAccessor {

	/**
	 * Reads table structure for the given $record from the database
	 *
	 * Associative array must be returned.
	 *
	 *	array(
	 *		"id" => "integer",,
	 *		"title" => "character varying(255)",
	 *		"figure" => "character(1)",
	 *		"an_integer" => "integer",
	 *		"a_big_integer" => string(6) "bigint",
	 *		"price" => string(13) "numeric(20,2)",
	 *		"a_float" => "double precision",
	 *		"text" => "text",
	 *		"flag" => "boolean",
	 *		"binary_data2" => "bytea",
	 *		"create_date" => "date",
	 *		"create_time" => "timestamp without time zone"
	 *	)
	 */
	static function ReadTableStructure($record,$options);

	/**
	 * Converts database type into into the corresponding (nearly PHP like) internal type
	 *
	 *	$internal_type = TableRecord_DatabaseAccessor::DatabaseTypeToInternalType("character varying(255)"); // "string"
	 *	$internal_type = TableRecord_DatabaseAccessor::DatabaseTypeToInternalType("timestamp without time zone"); // "timestamp"
	 *
	 * Allowed internal types:
	 *
	 * - float
	 * - integer
	 * - timestamp
	 * - boolean
	 * - string
	 *
	 * Value of type timestamp will be truncated to the first 19 characters, i.e. substr($value,0,19). So "2019-01-10 17:07:23.52453+01" will be converted to "2019-01-10 17:07:23"
	 */
	static function DatabaseTypeToInternalType($database_type);
}

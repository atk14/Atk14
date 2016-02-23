<?php
class TableRecord_DatabaseAccessor_Postgresql implements iTableRecord_DatabaseAccessor {

	/**
	 * @ignore
	 */
	function setRecordValues($data_row,&$record_values,$record){

		$structure = $record->_getTableStructure();
	  
		// pretypovani hodnot hodnot...
		foreach($data_row as $_key => $_value){
			if($_value===null){
				// hodnota je NULL, nemusime nic typovat

			}elseif(preg_match("/^(numeric|double precision)/",$structure[$_key])){
				$_value=(float) $_value;

			}elseif(preg_match("/^integer|bigint|smallint/",$structure[$_key])){
				$_real = $_value;
				#in 32 system integer can overflow, but float can be sufficient 
				 $_real=(float) $_real;
				 $_value=(int) $_value;
				if($_value!=$_real){
					$_value = $_real;
				}
			}elseif(preg_match("/^timestamp/",$structure[$_key])){
				$_value = substr($_value,0,19);

			}elseif(preg_match("/^bool/",$structure[$_key])){
				$_value = $record->dbmole->parseBoolFromSql($_value);
			}

			$record_values[$_key] = $_value;
		}
	}

	/**
	 * Reads (physically) table structure from the database
	 *
	 * @ignore
	 */
	function readTableStructure($record,$options = array()){
		$query = "
			SELECT
				a.attname,
				format_type(a.atttypid, a.atttypmod) AS format
			FROM
				pg_catalog.pg_class c INNER JOIN
				pg_catalog.pg_namespace n ON (c.relnamespace = n.oid) INNER JOIN
				pg_catalog.pg_attribute a ON (a.attrelid = c.oid)
			WHERE
				n.nspname = 'public' AND
				c.relname = :table_name AND
				a.attisdropped = false AND
				a.attnum > 0
		";
		return $record->dbmole->selectIntoAssociativeArray($query,array(":table_name" => $record->getTableName()),$options);
	}
}

<?php
class TableRecord_DatabaseAccessor_Mysql implements iTableRecord_DatabaseAccessor {

	/**
	 * @ignore
	 */
	static function SetRecordValues($data_row,&$record_values,$record){

		$structure = $record->_getTableStructure();
	  
		// pretypovani hodnot...
		foreach($data_row as $_key => $_value){
			if($_value===null){
				// hodnota je NULL, nemusime nic typovat

			}elseif(preg_match("/^(decimal|float)/",$structure[$_key])){
				$_value=(float) $_value;

			}elseif(preg_match("/^int|bigint|tinyint/",$structure[$_key])){
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
	static function ReadTableStructure($record,$options = array()){
		$dbmole = $record->dbmole;

		$rows = $dbmole->selectRows(sprintf("DESCRIBE %s",$dbmole->escapeTableName4Sql($record->getTableName())));

		$out = array();
		foreach($rows as $row){
			$out[$row["Field"]] = $row["Type"];
		}

		return $out;
	}
}

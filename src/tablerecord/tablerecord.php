<?php
/**
 * TableRecord layer for postgresql.
 * TODO: to be rewritten as dependency injection
 *
 * @package Atk14
 * @subpackage TableRecord
 * @filesource
 */

/**
 * TableRecord layer for postgresql.
 *
 * Base for each model.
 * ORM framework.
 *
 * @package Atk14
 * @subpackage TableRecord
 */
class TableRecord extends TableRecord_Base{

	/**
	 * Constructor
	 *
	 * @see TableRecord_Base::TableRecord_Base()
	 * @param mixed $table_name_or_options
	 * @param array $options
	 */
	function __construct($table_name_or_options = null,$options = array()){
		parent::__construct($table_name_or_options,$options);
	}

	/**
	 * @ignore
	 */
	function _setRecordValues($row){
	  
		// pretypovani hodnot hodnot...
		foreach($row as $_key => $_value){
			if($_value===null){
				// hodnota je NULL, nemusime nic typovat

			}elseif(preg_match("/^(numeric|double precision)/",$this->_TableStructure[$_key])){
				$_value=(float) $_value;

			}elseif(preg_match("/^integer|bigint|smallint/",$this->_TableStructure[$_key])){
				$_real = $_value;
				#in 32 system integer can overflow, but float can be sufficient 
				 $_real=(float) $_real;
				 $_value=(int) $_value;
				if($_value!=$_real){
					$_value = $_real;
				}
			}elseif(preg_match("/^timestamp/",$this->_TableStructure[$_key])){
				$_value = substr($_value,0,19);

			}elseif(preg_match("/^bool/",$this->_TableStructure[$_key])){
				$_value=$this->_dbmole->parseBoolFromSql($_value);

			}

			$this->_RecordValues[$_key] = $_value;
		}

		isset($row[$this->_IdFieldName]) && ($this->_Id = $this->_RecordValues[$this->_IdFieldName]);
	}

	/**
	 * Reads table structure.
	 *
	 * @ignore
	 */
	function _readTableStructure($options = array()){
		static $STORE;

		if(!isset($STORE)){ $STORE = array(); }
		if(isset($STORE[$this->_TableName])){ $this->_TableStructure = $STORE[$this->_TableName]; return; }
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
		$result = $this->_dbmole->selectRows($query,array(":table_name" => $this->_TableName),$options);
		foreach($result as $row){
			$this->_TableStructure[$row["attname"]] = $row["format"];
		}
		$STORE[$this->_TableName] = $this->_TableStructure;
	}
}

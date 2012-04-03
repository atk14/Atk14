<?php
/**
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 */

/**
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class TableRecord extends TableRecord_Base{

	function TableRecord($table_name = null,$options = array()){
		parent::TableRecord_Base($table_name,$options);
	}

	/**
	 * Creates an object of a class and reads in values from table.
	 *
	 * Method takes record $id, finds corresponding record and reads its values into newly created object.
	 *
	 * This method is used in a descendants {@link GetInstanceById()} method.
	 * <code>
	 * class inobj_Article extends TableRecord{
	 *	//...
	 *	function GetInstanceById($id,$options = array()){
	 *		return TableRecord::_GetInstanceById("inobj_Article",$id,$options);
	 *	}
	 *	//...
	 *	}
	 * </code>
	 *
	 *
	 * @static
	 * @access protected
	 * @param string $class_name	ie. "inobj_Article"
	 * @param mixed $id						identifikator zaznamu v tabulce; integer, string nebo pole
	 * @return TableRecord	resp. tridu, ktera je urcena v $class_name
	 */
	static function _GetInstanceById($class_name,$id,$options = array()){
		$out = new $class_name();
		return $out->find($id,$options);
	}

	/**
	 * Creates a record in a table
	 *
	 * Method takes array of values and creates a record in a table.
	 * Then returns an object of given class.
	 *
	 *
	* Tuto metodu pouzije pouzije v implementaci metody CreateNewRecord().
	* Pouzije ji nasledujicim zpusobem:
	*		class inobj_Article extends TableRecord{
	*			//...
	*			function CreateNewRecord($values,$options = array()){
	*				return TableRecord::_CreateNewRecord("inobj_Article",$values,$options);
	*			}
	*			//...
	*		}
	*
	*
	* @static
	* @access private
	* @param string $class_name					id. "inobj_Article"
	* @param array $values							
	* @return TableRecord
	*/
	static function _CreateNewRecord($class_name,$values,$options = array()){
		$out = new $class_name();
		return $out->_insertRecord($values,$options);
	}

	/**
	 *
	 * @access private
	 */
	function _setRecordValues($row){

		// pretypovani hodnot hodnot...
		foreach($row as $_key => $_value){

			if($_value===null){
				// hodnota je NULL, nemusime nic typovat

			}elseif(preg_match("/^(numeric|double precision)/",$this->_TableStructure[$_key])){
				settype($_value,"float");

			}elseif(preg_match("/^integer/",$this->_TableStructure[$_key])){
				$_real = $_value;
				#in 32 system integer can overflow, but float can be sufficient 
				settype($_real,"float");
				settype($_value,"integer");
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
	 * @access private
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
				c.relname = '".pg_escape_string($this->_TableName)."' AND
				a.attisdropped = false AND
				a.attnum > 0
		";
		$result = $this->_dbmole->selectRows($query,array(),$options);
		foreach($result as $row){
			$this->_TableStructure[$row["attname"]] = $row["format"];
		}
		$STORE[$this->_TableName] = $this->_TableStructure;
	}



	/*
	function _prepareValuesForSql($values,$skip_preparing_for = array()){
		settype($values,"array");
		settype($skip_preparing_for,"array");

		$out = array();
		while(list($field,$value) = each($values)){
			settype($field,"string");

			if(in_array($field,$skip_preparing_for)){
				$out[$field] = $value;
				continue;
			}

			if(!isset($value)){
				$_value = "NULL";
			}elseif(is_int($value) || is_float($value)){
				$_value = $value;
			}else{
				$_value = "'".pg_escape_string($value)."'";
			}
			$out[$field] = $_value;
		}
		return $out; 
	}
	*/
}

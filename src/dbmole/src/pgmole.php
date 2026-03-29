<?php
class PgMole Extends DbMole{

	protected $_AffectedRows = null;

	protected $_PreparedStatements = array();

	/**
	* Vrati instanci objektu pro danou konfiguraci.
	* Vraci vzdy stejny objekt pro stejnou konfiguraci.
	*
	* @static
	* @access public
	* @param string $configuration_name		"default"
	* @return PgMole									nebo null
	*/
	static function &GetInstance($configuration_name = "default",$options = array()){
		$options["class_name"] = "PgMole";
		return parent::GetInstance($configuration_name,$options);
	}

	function getDatabaseType(){ return "postgresql"; }

	function _disconnectFromDatabase(){
		$connection = $this->_getDbConnect();
		$conn_key = $this->_getConnectionKey($connection);
		unset($this->_PreparedStatements[$conn_key]);
		pg_close($connection);
	}
	

	/**
	* Provede spusteni SQL query a pole nalezenych zaznamu.
	* Vrati pole asociativnich poli.
	*
	* @access public
	* @param string $query
	* @param array $bind_ar
	* @param array $options
	* @return array						pole asociativnich poli; null v pripade chyby
	*/
	function selectRows($query,$bind_ar = array(), $options = array()){
		$options = array_merge(array(
			"limit" => null,
			"offset" => null,
			"avoid_recursion" => false,
		),$options);

		if(!$options["avoid_recursion"]){
			return $this->_selectRows($query,$bind_ar,$options);
		}


		if(isset($options["offset"]) || isset($options["limit"])){
			if(!isset($options["offset"])){ $options["offset"] = 0; }
			$_cond = array();
			if(isset($options["offset"])){
				$_cond[] = "OFFSET :offset____";
				$bind_ar[":offset____"] = $options["offset"];
			}
			if(isset($options["limit"])){
				$_cond[] = "LIMIT :limit____";
				$bind_ar[":limit____"] = $options["limit"];
			}
			$query = "
				SELECT * FROM (
					$query
				)q____ ".join(" ",$_cond)."	
			";
		}

		$result = $this->executeQuery($query,$bind_ar,$options);

		if(!$result){ return null; }

		$out = array();

		$num_rows = pg_num_rows($result);

		for($i=0;$i<$num_rows;$i++){
			$row = pg_fetch_row($result,$i,PGSQL_ASSOC);
			$out[] = $row;
		}
		pg_free_result($result);
		reset($out);
		return $out;
	}

	function selectSequenceNextval($sequence_name){
		return $this->selectSingleValue("SELECT NEXTVAL(".$this->escapeString4Sql($sequence_name).")");
	}

	function selectSequenceCurrval($sequence_name){
		return $this->selectSingleValue("SELECT CURRVAL(".$this->escapeString4Sql($sequence_name).")");
	}

	function _getAffectedRows(){
		return $this->_AffectedRows;
	}

	function _getDbLastErrorMessage(){
		$connection = $this->_getDbConnect();
		if($connection && ($err = pg_last_error($connection))){
			return "pg_last_error: $err";
		}
	}

	function _freeResult(&$result){
		return pg_free_result($result);
	}

	function escapeString4Sql($s){
		return "'".pg_escape_string($this->_getDbConnect(), $s)."'";
	}

	function escapeColumnName4Sql($column_name){
		static $cache = array();
		$c_key = (string)$column_name;
		if(!isset($cache[$c_key])){
			$cache[$c_key] = pg_escape_identifier($this->_getDbConnect(), $column_name);
		}
		return $cache[$c_key];
	}

	function escapeTableName4Sql($table_name){
		// Handling the schema.table entry
		if(strpos($table_name,'.') !== false){
			list($schema, $table) = explode('.', $table_name);
			return $this->escapeColumnName4Sql($schema).".".$this->escapeColumnName4Sql($table);
		}
		return $this->escapeColumnName4Sql($table_name);
	}

	function _executeQuery(){
		$query = $this->_Query;

		if(
			!DBMOLE_USE_PREPARED_STATEMENTS ||
			!$this->_BindAr ||
			strpos(trim($query),';')!==false // multiple commands must be processed using pg_query
		){
			return parent::_executeQuery();
		}

		$bind_keys = array_keys($this->_BindAr);
		$positional_values = array_values($this->_BindAr);
		$positional_values = array_map(function($value){
			if(is_object($value)){ $value = $value->getId(); }
			if(is_null($value)){ return $value; }
			if(is_bool($value)){ return $value ? "t" : "f"; }
			return (string)$value;
		},$positional_values);

		$bind_replacements = array_keys($bind_keys); // [0,1,2...]
		$bind_replacements = array_map(function($i){ return "$".($i+1); },$bind_replacements); // ["$1","$2","$3"...]
		$bind_replacements = array_combine($bind_keys,$bind_replacements); // [":firstname" => "$1", ":lastname" => "$2"...]

		$positional_query = strtr($query,$bind_replacements);

		$connection = $this->_getDbConnect();
		$conn_key = $this->_getConnectionKey($connection);

		if(!isset($this->_PreparedStatements[$conn_key])){
			$this->_PreparedStatements[$conn_key] = array();
		}

		$stmt_name = "dbmole_".sha1($positional_query);
		if(!isset($this->_PreparedStatements[$conn_key][$stmt_name])){
			$result = pg_prepare($connection, $stmt_name, $positional_query);
			if(!$result){
				$this->_raiseDBError("failed to prepare SQL query");
				return null;
			}
			$this->_PreparedStatements[$conn_key][$stmt_name] = true;
		}
		$result = pg_execute($connection, $stmt_name, $positional_values);

		if(!$result){
			$this->_raiseDBError("failed to execute prepared SQL query");
			return null;
		}

		return $result;
	}

	function _runQuery($query){
		$connection = $this->_getDbConnect();
		$result = pg_query($connection,$query);
		$this->_AffectedRows = $result!==false ? pg_affected_rows($result) : null;
		return $result;
	}

	function _getDatabaseName(){
		$connection = $this->_getDbConnect();
		return pg_dbname($connection);
	}

	function _getDatabaseServerVersion(){
		$connection = $this->_getDbConnect();
		$ver = pg_version($connection);
		return $ver["server"];
	}

	function _getDatabaseClientVersion(){
		$connection = $this->_getDbConnect();
		$ver = pg_version($connection);
		return $ver["client"];
	}

	function _getConnectionKey($connection){
		if(!is_object($connection)){
			return (int)$connection;
		}
		return spl_object_hash($connection);
	}
}

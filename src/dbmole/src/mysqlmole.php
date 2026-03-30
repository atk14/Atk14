<?php
class MysqlMole extends DbMole{

	static function &GetInstance($configuration_name = "default",$options = array()){
		$options["class_name"] = "MysqlMole";
		return parent::GetInstance($configuration_name,$options);
	}

	// MySQL doesn't use sequencies, therefore methods selectSequenceNextval and selectSequenceCurrval are not covered and return nulls.
	function usesSequencies(){ return false; }

	function selectInsertId(){
		$connection = $this->_getDbConnect();
		return mysqli_insert_id($connection);
	}

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
			if(isset($options["limit"])){
				$_cond[] = "LIMIT :limit____";
				$bind_ar[":limit____"] = $options["limit"];
			}
			if(isset($options["offset"])){
				$_cond[] = "OFFSET :offset____";
				$bind_ar[":offset____"] = $options["offset"];
			}
			$query = "$query ".join(" ",$_cond);
		}

		$result = $this->executeQuery($query,$bind_ar,$options);

		if(!$result){ return null; }

		$out = array();

		while($row = mysqli_fetch_assoc($result)){
			$out[] = $row;
		}
		mysqli_free_result($result);
		reset($out);
		return $out;
	}

	function escapeColumnName4Sql($column_name){
		static $cache = array();
		$c_key = (string)$column_name;
		if(!isset($cache[$c_key])){
			$column_name = str_replace("\0", "", $column_name); // remove null byte
			$column_name = str_replace("`","``",$column_name);
			$column_name = "`$column_name`";
			$cache[$c_key] = $column_name;
		}
		return $cache[$c_key];
	}

	function escapeTableName4Sql($table_name){
		// Handling the schema.table entry
		if (strpos($table_name,'.') !== false) {
			list($schema, $table) = explode('.',$table_name);
			return $this->escapeColumnName4Sql($schema).".".$this->escapeColumnName4Sql($table);
		}
		return $this->escapeColumnName4Sql($table_name);
	}

	function escapeString4Sql($s){
		$connection = $this->_getDbConnect();
		return "'".mysqli_real_escape_string($connection,$s)."'";
	}

	function _getDbLastErrorMessage(){
		$connection = $this->_getDbConnect();
		if($connection && ($err = mysqli_error($connection))){
			return "mysqli_error: $err";
		}
	}

	function _freeResult(&$result){
		if(is_bool($result)){ return true; }
		return mysqli_free_result($result);
	}

	function _runQuery($query){
		$connection = $this->_getDbConnect();
		return mysqli_query($connection,$query);
	}

	function _disconnectFromDatabase(){
		$connection = $this->_getDbConnect();
		mysqli_close($connection);
	}

	function getAffectedRows(){
		$connection = $this->_getDbConnect();
		$out = mysqli_affected_rows($connection);
		if($out===-1){
			return null;
		}
		return $out;
	}

	/**
	 * HACK for Atk14Migration
	 *
	 * TODO: to be removed or rewritten somehow
	 */
	function multiQuery($query){
		$connection = $this->_getDbConnect();
		$result = mysqli_multi_query($connection,$query);

		if(!$result){
			$this->_raiseDBError("failed to execute SQL query");
			return null;
		}

		do {
			if ($result = $connection->store_result()) {
				$result->free();
			}
		} while ($connection->next_result());

		return $result;
	}

	function _getDatabaseServerVersion(){
		$connection = $this->_getDbConnect();
		$ver = mysqli_get_server_version($connection);
		$major = floor($ver/10000);
		$minor = floor(($ver - ($major * 10000))/100);
		$patch = $ver % 100;
		$ver = "$major.$minor.$patch";
		return $ver;
	}

	function _getDatabaseClientVersion(){
		$ver = mysqli_get_client_version();
		$major = floor($ver/10000);
		$minor = floor(($ver - ($major * 10000))/100);
		$patch = $ver % 100;
		$ver = "$major.$minor.$patch";
		return $ver;
	}
}

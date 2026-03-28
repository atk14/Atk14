<?php
class SqlsrvMole extends DbMole {

	protected $_AffectedRows = null;

	function usesSequencies(){ return true; }

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
				$_cond[] = "OFFSET :offset____ ROWS";
				$bind_ar[":offset____"] = $options["offset"];
			}
			if(isset($options["limit"])){
				$_cond[] = "FETCH NEXT :limit____ ROWS ONLY";
				$bind_ar[":limit____"] = $options["limit"];
			}
			$query = "$query ".join(" ",$_cond);
		}

		$result = $this->executeQuery($query,$bind_ar,$options);

		if(!$result){ return null; }

		$out = array();

		while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
			$out[] = $row;
		}
		sqlsrv_free_stmt($result);
		reset($out);
		return $out;
	}

	function selectSequenceNextval($sequence_name){
		return $this->selectSingleValue("SELECT NEXT VALUE FOR $sequence_name");
	}

	function selectSequenceCurrval($sequence_name){
		return $this->selectSingleValue("SELECT current_value FROM sys.sequences WHERE name=".$this->escapeString4Sql($sequence_name));
	}
	
	function escapeString4Sql($s){
		return "'".strtr($s,array(
			"'" => "''",
		))."'";
	}

	function _begin(){
		$connection = $this->_getDbConnect();
		return sqlsrv_begin_transaction($connection);
	}

	function _commit(){
		$connection = $this->_getDbConnect();
		return sqlsrv_commit($connection);
	}

	function _rollback(){
		$connection = $this->_getDbConnect();
		return sqlsrv_rollback($connection);
	}

	function _freeResult(&$result){
		return sqlsrv_free_stmt($result);
	}

	function _runQuery($query){
		$connection = $this->_getDbConnect();
		$result = sqlsrv_query($connection,$query);
		$this->_AffectedRows = $result!==false ? sqlsrv_rows_affected($result) : null;
		return $result;
	}

	function _disconnectFromDatabase(){
		$connection = $this->_getDbConnect();
		sqlsrv_close($connection);
	}

	function getAffectedRows(){
		return $this->_AffectedRows;
	}

	function _getDbLastErrorMessage(){
		//$connection = $this->_getDbConnect();
		if($errs = sqlsrv_errors()){
			$messages = array();
			foreach($errs as $err){
				$messages[] = "$err[message] (SQLSTATE=$err[SQLSTATE], code=$err[code])";
			}
			return "sqlsrv_errors: ".join(", ",$messages);
		}
	}

	function _getDatabaseServerVersion(){
		$connection = $this->_getDbConnect();
		$info = sqlsrv_server_info($connection);

		// "15.00.4053" -> "15.0.4053"
		$version_ary = explode(".",$info["SQLServerVersion"]);
		foreach($version_ary as &$item){
			$item = (int)$item;
		}

		return join(".",$version_ary);
	}

	function _getDatabaseClientVersion(){
		$connection = $this->_getDbConnect();
		$info = sqlsrv_client_info($connection);

		// "17.08.0001" -> "17.8.1"
		$version_ary = explode(".",$info["DriverVer"]);
		foreach($version_ary as &$item){
			$item = (int)$item;
		}

		return join(".",$version_ary);
	}
}

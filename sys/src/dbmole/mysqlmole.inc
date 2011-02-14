<?
class MysqlMole extends DbMole{
	function &GetInstance($configuration_name = "default"){
		return parent::GetInstance($configuration_name,"MysqlMole");
	}

	// MySQL doesn't use sequencies, therefore methods selectSequenceNextval and selectSequenceCurrval are not covered and return nulls.
	function usesSequencies(){ return false; }

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

		while($row = mysql_fetch_assoc($result)){
			$out[] = $row;
		}
		mysql_free_result($result);
		reset($out);
		return $out;
	}

	function escapeString4Sql($s){
		return "'".mysql_escape_string($s)."'";
	}

	function _getDbLastErrorMessage(){
		return "mysql_error: ".mysql_error($this->_DbConnect);
	}

	function _freeResult(&$result){
		if(is_bool($result)){ return true; }
		return mysql_free_result($result);
	}

	function _runQuery($query){
		return mysql_query($query,$this->_DbConnect);	
	}

	function _disconnectFromDatabase(){
		mysql_close($this->_DbConnect);
	}
}
?>

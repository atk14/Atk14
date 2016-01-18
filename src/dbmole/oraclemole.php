<?php
/**
 *
 *
 * Pouziti:
 *
 *		DbMole::RegisterErrorHandler("_oracle_mole_error_handler"); //registrace error handleru pro tridu OracleMole
 *
 * 	$mole = &OracleMole::GetInstance();
 *		$mole_ov = &OracleMole::GetInstance("ov");
 *
 * 	$mole->doQuery("BEGIN active_users(); END;");
 *		$mole->commit();
 *
 *		// nacteni jedine hodnoty
 *		echo "celk. pocet clanku: ";
 *		echo $mole->selectSingleValue("SELECT COUNT(*) FROM articles","integer");
 *
 *		// nacteni jednoho radku
 * 	$row = $mole->selectFirstRow("SELECT * FROM articles WHERE id=:id",array(":id" => 2223445));
 *		echo $row["name"];
 *
 *		// nacteni vice radku
 * 	$rows = $mole->selectRows("SELECT * FROM articles WHERE source_id=:source_id",array(":source_id" => 112233"),array("limit" => 20,"offset" => 40));
 *		foreach($rows as $row){
 *			echo "$row[id]: $row[name]\n";
 *			echo "body:\n";
 *			echo $row["body"];	// obsah CLOBu a BLOBu bude automaticky fetchnut
 *			echo "\n-------------------\n";
 *		}
 *
 * 	// TODO: dodelat moznost bindovat descriptoru pro large objecty
 * 	// Nyni lze pouzit nesledujici:
 *		$stmt = $mole->executeQuery(
 *			"UPDATE articles SET bode=EMPTY_CLOB() WHERE id=:id RETURNING body INTO :body",
 *			array(":id" => 3443),
 *			array("execute_statement" => false)
 *		);
 *		// A zde uz si nabindovani :body udelat pekne rucne!
 *		OCIFreeStatement($stmt);
 *
 * @package Atk14
 * @subpackage Database
 * @filesource
 *
 */

/**
 * @package Atk14
 * @subpackage Database
 * @filesource
 */
class OracleMole extends DbMole{
	var $_LastOracleStatement = null;

	/**
	* Vrati instanci objektu pro danou konfiguraci.
	* Vraci vzdy stejny objekt pro stejnou konfiguraci.
	*
	* @static
	* @access public
	* @param string $configuration_name		"default" nebo "ov"
	* @return DbMole									nebo null
	*/
	static function &GetInstance($configuration_name = "default",$options = array()){
		$options["class_name"] = "OracleMole";
		return parent::GetInstance($configuration_name,$options);
	}

	function _disconnectFromDatabase(){
		$connection = $this->_getDbConnect();
		OCILogOff($connection);
	}
	
	/**
	* Realizuje spusteni query.
	* Vrati statement.
	* 
	* V poli $options lze nastavit mod spusteni prikazu:
	*		$options["mode"] = OCI_DEFAULT
	*		$options["mode"] = OCI_COMMIT_ON_SUCCESS
	* Defaultni je OCI_DEFAULT.
	*
	* @access public
	* @param string $query
	* @param string $options
	* @return statement						nebo null v pripade
	*/
	function _executeQuery(){
		$query = &$this->_Query;
		$bind_ar = &$this->_BindAr;
		$options = &$this->_Options;

		foreach($bind_ar as &$value){
			if(is_object($value)){
				$value = $value->getId();
			}
			if(is_bool($value)){
				$value = $this->escapeBool4Sql($value);
			}	
		}

		// toto odstranuje z SQL prikazu znak \r, ktery zpusobuje problemy
		// na ihnedu toto resila fce remover...
		$query = str_replace("\r"," ",$query);

		// nastaveni defaultnich hodnot v options
		$options["mode"] = isset($options["mode"]) ? $options["mode"] : OCI_DEFAULT;
		$options["limit"] = isset($options["limit"]) ? (int)$options["limit"] : null;
		$options["offset"] = isset($options["offset"]) ? (int)$options["offset"] : null;
		$options["bind_values"] = isset($options["bind_values"]) ? (bool)$options["bind_values"] : true;
		$options["execute_statement"] = isset($options["execute_statement"]) ? (bool)$options["execute_statement"] : true;
		$options["clobs"] = isset($options["clobs"]) ? (array)$options["clobs"] : array();			// $options["clobs"] = array(":body",":perex");
		$options["blobs"] = isset($options["blobs"]) ? (array)$options["blobs"] : array();			// $options["blobs"] = array(":binary_body")

		if(isset($options["offset"]) || isset($options["limit"])){
			if(!isset($options["offset"])){ $options["offset"] = 0; }
			$_cond = array();
			if(isset($options["offset"])){
				$_cond[] = "rnum____>:offset____";
				$bind_ar[":offset____"] = $options["offset"];
			}
			if(isset($options["limit"])){
				$_cond[] = "rnum____<=:limit____";
				$bind_ar[":limit____"] = $options["offset"] + $options["limit"];
			}

			// HACK: misto ROWNUM je tu pouzit ROW_NUMBER()
			// v 10g toto zacalo zlobit v pripade, ze $query obsahovalo rovnez omezeni na ROWNUM
			$query = "
				SELECT * FROM (
					SELECT
						q____.*,
						-- ROWNUM AS rnum____
						ROW_NUMBER() OVER(ORDER BY ROWNUM) AS rnum____
					FROM
						($query)q____
				) WHERE ".join(" AND ",$_cond)."
			";
		}
	
		//if(preg_match("/(INSERT|UPDATE).*RETURNING/s",$query)){
		//	echo $query."\n";
		//}

		$this->_freeLastOracleStatement();

		// parsovani dotazu
		$connection = $this->_getDbConnect();
		$stmt = OCIParse($connection,$query);
		$this->_LastOracleStatement = &$stmt;
		if(!$stmt){
			$this->_raiseDBError("OCIParse failed");
			return null;
		}

		// bindovani promennych
		$lobs = array();
		if($options["bind_values"]){
			foreach(array_keys($bind_ar) as $key){
				//if(is_object($bind_ar[$key])){ $bind_ar[$key] = $bind_ar[$key]->getId(); }
				// bindovani large objektu
				// v podmince je zamerne $_typ=, aby doslo k priprazeni spravneho typu
				if((in_array($key,$options["blobs"]) && $_type=OCI_B_BLOB) || (in_array($key,$options["clobs"]) && $_type=OCI_B_CLOB)){
					$lobs[$key] = OCINewDescriptor($connection,OCI_D_LOB);
					$_stat = OCIBindByName($stmt,$key,$lobs[$key],-1,$_type);
					if(!$_stat){
						$this->_raiseDBError("bind of the >>$key<< with new descriptor failed");
						return null;
					}
					continue;
				}
				// bindovani normalnich hodnot
				$_stat = OCIBindByName($stmt,$key,$bind_ar[$key],-1);
				if(!$_stat){
					$this->_raiseDBError("bind of the >>$key<< with value >>$bind_ar[$key]<< failed");
					return null;
				}
			}
		}

		// spusteni prikazu
		if($options["execute_statement"]){

			$_stat = OCIExecute($stmt,$options["mode"]);
			if(!$_stat){
				$this->_raiseDBError("the execution of the SQL query failed");
				return null;
			}
			
			// ulozeni obsahu large objectu
			foreach(array_keys($lobs) as $key){	
				if(strlen($bind_ar[$key])>0){
					$lob = &$lobs[$key];
					// PHP4 nezna metody write();
					$_stat = method_exists($lob,"write") ? $lob->write($bind_ar[$key]) : $lob->save($bind_ar[$key]);
					if(!$_stat){
						$this->_raiseDBError("can't save lob data into $key: ".$this->_getDbLastErrorMessage());
						return null;
					}
				}
			}
		}

		return $stmt;
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
			"lowercase_field_names" => true,
			"limit" => null,
			"offset" => null,
			"avoid_recursion" => false,
		),$options);

		if(!$options["avoid_recursion"]){
			return $this->_selectRows($query,$bind_ar,$options);
		}

		$stmt = $this->executeQuery($query,$bind_ar,$options);

		if(!$stmt){ return null; }

		$out = array();

		while(OCIFetchInto($stmt,$row,OCI_ASSOC + OCI_RETURN_NULLS)){
			unset($row["RNUM____"]); // vpripade, ze bylo pouzito omezeni vybery pomoci $options["limit"] nebo $options["offset"], je ve vysledu RNUM____
			$_row = array();
			foreach($row as $_key => $_value){
				if(is_object($_value)){
					$_value = $_value->load();
					// toto je maly hack: pokud najdeme CLOB nebo BLOB o nulove delce, nastavime hodnotu na string
					// tim padem to bude stejne jako u policek typu VARCHAR
					if(strlen($_value)==0){ $_value = null; }
				}
				if($options["lowercase_field_names"]){
					$_key = strtolower($_key);
				}
				$_row[$_key] = $_value;
			}
			$out[] = $_row;
		}
		return $out;
	}

	function SelectSequenceNextval($sequence_name){
		return $this->selectSingleValue("SELECT $sequence_name.NEXTVAL FROM DUAL"); //
	}

	function SelectSequenceCurrval($sequence_name){
		return $this->selectSingleValue("SELECT $sequence_name.CURRVAL FROM DUAL"); //
	}

	/**
	* Prekryta metoda.
	* Zde se mohou v $options definovat $options["clobs"] a $options["blobs"].
	*
	*		$dbmole->insertIntoTable("articles",array(
	*			"id" => $dbmole->selectSequenceNextval('se$articles_id'),
	*			"name" => "nazev clanku",
	*			"perex" => "perex clanku",
	*			"body" => "telicko clanku",
	*			"create_date" => "2008-01-02 12:33:23",
	*			"update_date" => "SYSDATE"
	*		),array(
	*			"clobs" => array("perex","body"),
	*			"do_not_escape" => array("update_date")
	*		));
	*
	* Pozor!!!
	* V polich $options["clobs"] $options["blobs"] se zde uvadeji nazvy poli (nikoli bind klic s prefixem :).
	* Uvnitr fce jsou nazvy poli prevedeny na bind klice.
	*/
	function insertIntoTable($table_name,$values,$options = array()){
		settype($table_name,"string");
		settype($values,"array");
		settype($options,"array");

		$options["clobs"] = isset($options["clobs"]) ? (array)$options["clobs"] : array();			// $options["clobs"] = array("body","perex");
		$options["blobs"] = isset($options["blobs"]) ? (array)$options["blobs"] : array();			// $options["blobs"] = array("binary_body")
		$options["do_not_escape"] = isset($options["do_not_escape"]) ? (array)$options["do_not_escape"] : array(); // $options["do_not_escape"] = array("create_date")

		$clobs = $options["clobs"];
		$blobs = $options["blobs"];
		$do_not_escape = $options["do_not_escape"];

		$options["clobs"] = array();
		$options["blobs"] = array();
		$options["do_not_escape"] = array();

		$table_fields = array();
		$table_values = array();
		$bind_ar = array();
		$lob_fields = array();
		$lob_bind_keys = array();

		foreach($values as $field => $value){	
			$table_fields[] = $field;

			if(in_array($field,$do_not_escape)){
				$table_values[] = $value;
				continue;
			}

			$_key = ":$field";
			$bind_ar[$_key] = $value;

			if(in_array($field,$clobs)){
				$table_values[] = "EMPTY_CLOB()";
				$options["clobs"][] = $_key;
				$lob_fields[] = $field;
				$lob_bind_keys[] = $_key;
			}elseif(in_array($field,$blobs)){
				$table_values[] = "EMPTY_BLOB()";
				$options["blobs"][] = $_key;
				$lob_fields[] = $field;
				$lob_bind_keys[] = $_key;
			}else{
				$table_values[] = $_key;
			}
		}
		
		$query = "INSERT INTO $table_name (\n  ".join(",\n  ",$table_fields)."\n) VALUES(\n  ".join(",\n  ",$table_values)."\n)";
		if(sizeof($lob_fields)>0){
			$query .= " RETURNING ".join(", ",$lob_fields)." INTO ".join(", ",$lob_bind_keys);
		}
		return $this->doQuery($query,$bind_ar,$options);
	}

	function _begin(){
		return true;
	}

	/**
	* Provede Commit.
	*
	* @access public
	* @return bool				true -> uspesne provedeno
	*											false -> doslo k chybe
	*/
	function _commit(){
		return $this->_doCommitOrRollback("COMMIT");
	}

	/**
	* Provede Rollback.
	*
	* @access public
	* @return bool				true -> uspesne provedeno
	*											false -> doslo k chybe
	*/
	function _rollback(){
		return $this->_doCommitOrRollback("ROLLBACK");
	}

	/**
	* Provede Commit nebo Rollback.
	*
	* @access private
	* @param string $action				"COMMIT" nebo "ROLLBACK"
	* @return bool								true -> uspesne provedeno
	*															false -> doslo k chybe
	*/
	function _doCommitOrRollback($action){
		settype($action,"string");
		$function_name = "Oci$action";
		$this->_reset();
		$connection = $this->_getDbConnect();
		$_stat = $function_name($connection);
		if(!$_stat){
			$this->_raiseDBError("$function_name failed");
			return false;
		}
		return true;
	}

	function _getDbLastErrorMessage(){
		$error = $this->_LastOracleStatement ? OCIError($this->_LastOracleStatement) : null;
		if(is_array($error) && isset($error["message"]) && strlen($error["message"])>0){
			return "OCIError[message]: $error[message]";
		}
	}

	/**
	 * Does nothing
	 *
	 * We need the last oracle statment for getAffectedRows()
	 */
	function _freeResult(&$stmt){
		return null;
	}

	function _freeLastOracleStatement(){
		if($this->_LastOracleStatement && !($out = OCIFreeStatement($this->_LastOracleStatement))){
			$this->_raiseDBError("Can't do OCIFreeStatement: ".$this->_getDbLastErrorMessage());
		}

		$this->_LastOracleStatement = null;
	}

	/**
	 * echo $dbmole->escapeBool4Sql(true); // "Y"
	 * echo $dbmole->escapeBool4Sql(false); // "N"
	 */
	function escapeBool4Sql($value){
		return $value ? DBMOLE_ORACLE_TRUE : DBMOLE_ORACLE_FALSE;
	}

	function _getAffectedRows(){
		if($this->_LastOracleStatement){
			return oci_num_rows($this->_LastOracleStatement);
		}
	}
}

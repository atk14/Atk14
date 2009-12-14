<?
/**
* Zachytavani chyb:
*		DbMole::RegisterErrorHandler("dbmole_error_handler");
*		function dbmole_error_handler($dbmole){
*			echo "drahy uzivateli, bohuzel doslo k chybe";
*			$dbmole->sendErrorReportToEmail("admin@test.cz");
*			$dbmole->logErrorReport();
*			exit;
*		}
*
* Statistika:
*
*		define("DBMOLE_COLLECT_STATICTICS",true);
*		$dbmole = &OracleMole::GetInstance();
*		echo $dbmole->getStatistics();
*/
class DbMole{
	var $_ConfigurationName = "";
	var $_DbConnect = null;

	var $_ErrorMessage = null;
	var $_ErrorHangler = null;

	var $_Query = "";
	var $_BindAr = array();
	var $_Options = array();

	var $_CacheDir = null;

	/**
	* Vrati instanci objektu pro danou konfiguraci.
	* Vraci vzdy stejny objekt pro stejnou konfiguraci.
	*
	* $dbmole = &DbMole::GetInstance("default","OracleMole");
	*
	* Ale lepsi je volat primo tridu dedice:
	* $dbmole = &OracleMole::GetInstance("default");
	*/
	function &GetInstance($configuration_name,$class_name){
		static $instance_store_ar;

		settype($configuration_name,"string");
		settype($class_name,"string");

		$out = new $class_name();
		$db_type = $out->getDatabaseType();

		if(!isset($instance_store_ar)){ $instance_store_ar = array(); }
		if(!isset($instance_store_ar[$db_type])){ $instance_store_ar[$db_type] = array(); }
		
		if(!isset($instance_store_ar[$db_type][$configuration_name])){
			$out = new $class_name();
			$out->_ConfigurationName = $configuration_name;
			$instance_store_ar[$db_type][$configuration_name] = &$out;
		}
		
		return $instance_store_ar[$db_type][$configuration_name];
	}

	function getDatabaseType(){
		if(preg_match("/^(.+)mole$/",strtolower(get_class($this)),$matches) && $matches[1]!="db"){
			return $matches[1];
		}
		return "unknown";
	}

	/**
	* Registrace nazvu funkce, ktera ma byt volana, dojde-li k chybe pri vykonavani SQL dotazu.
	* Jako parametr bude treto funkci predan object (resp. dedic) tridy DbMole.
	* 
	* Vraci nazev error handleru zaregistrovaneho predchozim volanim.
	*
	*	DbMole::RegisterErrorHandler("dbmole_error_handler");
	*	function dbmole_error_handler($dbmole){
	*		echo "drahy uzivateli, bohuzel doslo k chybe";
	*		$dbmole->sendErrorReportToEmail("admin@test.cz");
	*		$dbmole->logErrorReport();
	*		exit;
	*	}	
	*/
	function RegisterErrorHandler($function_name){
		return DbMole::_GetSetErrorHandlerFunction($function_name,true);
	}

	function getStatistics(){
		global $__DMOLE_STATISTICS__;

		if(!isset($__DMOLE_STATISTICS__)){ $__DMOLE_STATISTICS__ = array(); }

		$ar = array();

		$total_queries = 0;
		$total_time = 0.0;

		reset($__DMOLE_STATISTICS__);
		$counter = 1;
		while(list($q,$itms) = each($__DMOLE_STATISTICS__)){
			$total_queries += sizeof($itms);
			$current_query_time = 0.0;
			reset($itms);
			while(list(,$itm) = each($itms)){
				$total_time += $itm["time"];
				$current_query_time += $itm["time"];
			}
			$ar[$this->_formatSeconds($current_query_time).$counter] = array(
				"count" => sizeof($itms),
				"query" => $q,
				"time" => $current_query_time
			);
			$counter++;
			/*
			$out[] = "<h3>".sizeof($itms)."&times; (".$this->_formatSeconds($current_query_time)."s)</h3>";
			$out[] = "<pre>";
			$out[] = htmlspecialchars(str_replace("\t","  ",$q));
			$out[] = "</pre>";
			*/
		}

		krsort($ar,SORT_NUMERIC);

		$out = array();
		$out[] = "<div style=\"text-align: left;\">";
		$out[] = "<h3>total queries: $total_queries</h3>";
		$out[] = "<h3>total time: ".$this->_formatSeconds($total_time)."s</h3>";
		foreach($ar as $item){
			$percent = number_format((($item["time"]/$total_time)*100),1,".","");
			$time_per_single_query = $this->_formatSeconds($item["time"]/$item["count"])."s";
			$out[] = "<h3>$item[count]&times; ($percent%, $item[count]&times;$time_per_single_query=".$this->_formatSeconds($item["time"])."s)</h3>";
			$out[] = "<pre>";
			$out[] = htmlspecialchars(str_replace("\t","  ",$item["query"]));
			$out[] = "</pre>";
		}
		$out[] = "</div>";

		return join("\n",$out);
	}

	function _formatSeconds($sec){
		return number_format($sec,3,".","");
	}

	/**
	*
	* @static
	* @access private
	* @param string $function_name
	* @param bool $set									true -> ulezeni nazvu fce
	* @return string										aktualni jmeno (nebo predchozi pri nastavavovani) error handler funkce 
	*																		pokud je vracen prazdny string "", nema se nic volat
	*/
	function _GetSetErrorHandlerFunction($function_name = "",$set = false){
		static $_FUNCTION_NAME_;

		settype($set,"bool");
		settype($function_name,"string");

		$prev_function_name = "";
		if(isset($_FUNCTION_NAME_)){
			$prev_function_name = $_FUNCTION_NAME_;
		}
		
		if($set){
			$_FUNCTION_NAME_ = $function_name;
		}

		return $prev_function_name;
	}

	/**
	* Napoji se do databaze.
	*
	* Musi byt prekryto!
	*
	* @access private
	* @return bool					true -> uspesne napojeno
	*												false -> doslo k chybe
	*/
	function _connectToDatabase(){
		if(isset($this->_DbConnect)){ return true; }

		$this->_DbConnect = &dbmole_connection($this);
		if(!isset($this->_DbConnect)){
			$this->_raiseDBError(sprintf("can't connect to %s database with configuration '%s'",$this->getDatabaseType(),$this->getConfigurationName()));
			return false;
		}
		return true;
	}

	function _selectRows($query,&$bind_ar, $options = array()){
		$options = array_merge(array(
			"cache" => 0,
		),$options);
		$options["avoid_recursion"] = true; // protoze primo metoda selectRows() vola _selectRows() a naopak, mame tady tento ochranny parametr
		$cache = (int)$options["cache"];

		foreach($bind_ar as $k => $v){
			if(is_object($v)){ $bind_ar[$k] = $v->getId(); }
		}

		if($cache>0){
			$rows = $this->_readCache($cache,$query,$bind_ar,$options);
			if(is_array($rows)){
				return $rows;
			}
		}

		$rows = $this->selectRows($query,$bind_ar,$options);

		if($cache>0){
			$this->_writeCache($rows,$query,$bind_ar,$options);
		}
			
		return $rows;
	}

	/**
	* Zjiteni, zda doslo k chybe.
	* 
	* Z nekterych metod je mozne primo z navr. hodnoty zjistit,
	* zda doslo k chybe (commit(), rollback(), selectRows()).
	*
	* Z navratovych hodnot jinych fci to poznat nelze (selectSingleValue(),
	* selectFirstRow())
	*
	* Nicmene, pokud je zaregistrovana takova error handler funkce,
	* ktera ukonci beh skriptu (exit), neni treba v normalnim kodu
	* nic zjistovat.
	*
	* @access public
	* @return bool				true -> doslo k chybe
	*											false -> nedoslo k chybe
	*/
	function errorOccurred(){ return isset($this->_ErrorMessage); }

	/**
	* Gettery vhodne pro error_handler funkci.
	*/
	function getErrorMessage(){ return $this->_ErrorMessage; }
	function getQuery(){ return $this->_Query; }
	function getBindAr(){ return $this->_BindAr; }
	function getOptions(){ return $this->_Options; }
	function getConfigurationName(){ return $this->_ConfigurationName; }

	function getErrorReport(){
		$out = array();

		$out[] = "DbMole error report";
		$out[] = "";
		$out[] = "error message";
		$out[] = "-------------";
		$out[] = $this->getErrorMessage();
		$out[] = "";
		$out[] = "query";
		$out[] = "-----";
		$out[] = $this->getQuery();
		$out[] = "bind_ar";
		$out[] = "-------";
		$out[] = print_r($this->getBindAr(),true);
		if(isset($GLOBALS["_SERVER"]));{
			$out[] = "";
			$out[] = "server vars";
			$out[] = "-----------";
			$out[] = print_r($GLOBALS["_SERVER"],true);
		}
		if(isset($GLOBALS["_GET"]));{
			$out[] = "";
			$out[] = "get vars";
			$out[] = "--------";
			$out[] = print_r($GLOBALS["_GET"],true);
		}
		if(isset($GLOBALS["_POST"]));{
			$out[] = "";
			$out[] = "post vars";
			$out[] = "--------";
			$out[] = print_r($GLOBALS["_POST"],true);
		}
		return join("\n",$out);
	}

	function sendErrorReportToEmail($email_address,$options = array()){
		$options["report_failed_database_connection"] = isset($options["report_failed_database_connection"]) ? (bool)$options["report_failed_database_connection"] : false;

		if(!$options["report_failed_database_connection"] && preg_match("/^can't connect to database/",$this->getErrorMessage())){
			return;
		}

		mail($email_address,"DbMole: error report",$this->getErrorReport());
	}

	function logErrorReport(){
		error_log($this->getErrorReport());
	}


	function _initializeBeforeDoingSQL(){
		$this->_reset();
		$this->_connectToDatabase();
	}

	/**
	* Vyresetuje nektere stavy objektu.
	* Vola se pred provedenim SQL prikazu.
	* 
	* @access private
	*/
	function _reset(){
		$this->_ErrorMessage = null;
		$this->_Query = "";
		$this->_BindAr = array();
		$this->_Options = array();

		if(!isset($this->_CacheDir)){
			if(defined("TEMP")){
				$this->_CacheDir = TEMP;
			}else{
				$this->_CacheDir = "/tmp/";
			}
			$this->_CacheDir .= "/dbmole_cache/".$this->getDatabaseType()."/".$this->getConfigurationName()."/";
		}
	}

	/**
	* Je volano v pripade, ze dojde k chybe.
	*
	* 
	* 
	* @access private
	* @param string $message_prefix				ie. "OCIParse failed"
	*/
	function _raiseDBError($message){
    $this->_ErrorMessage = "$message";

		if(strlen($db_error = $this->_getDbLastErrorMessage())>0){
			$this->_ErrorMessage .= " ".$db_error;
		}

		$error_handler = DbMole::_GetSetErrorHandlerFunction();
		if(strlen($error_handler)>0){
			$error_handler($this);
		}else{
			$this->logErrorReport();
			exit;
		}

		return null;
	}

	/**
	* Provede spusteni SQL query.
	* 
	* @access public
	* @param string $query
	* @param array $bind_ar
	* @param array $options
	* @return bool									true -> v poradku porvedeno
	*																false -> chyba
	*/
	function doQuery($query,$bind_ar = array(), $options = array()){
		$result = $this->executeQuery($query,$bind_ar,$options);
		if(!$result){ return false; }
		$this->_freeResult($result);
		return true;
	}

	/**
	* Vrati asociativni pole prvniho nalezeneho zaznamu.
	* Nebo vrati null, pokud result neobsahuje ani jeden zaznam (nebo doslo k chybe).
	*
	* @access public
	* @param string $query
	* @param array $bind_ar
	* @param array $options
	* @return array							asociativni pole
	*/
	function selectFirstRow($query,$bind_ar = array(), $options = array()){
		$records = $this->_selectRows($query,$bind_ar,$options);
		if(!isset($records) || sizeof($records)==0){
			return null;
		}
		return $records[0];
	}
	
	/**
	* Vrati prvni hodnotu z prvniho nalezeneho radku.
	* Velmi vhodne pro dotazy typu "SELECT COUNT(*) AS count FROM articles WHERE source_date>SYSDATE"
	*
	* Pokud bude hodnota NULL, bude vracen null.
	*
	* V $options lze nastavit typ vracene hodnoty: $options = array("type" => "integer").
	* Protoze toto je bude pouzivane asi hodne, tak je mozne definovat $options primo jako string "integer".
	* Pak je preveden na pole array("type" => $options)
	*
	* Pouziti:
	*		$mole->selectSingleValue("SELECT COUNT(*) FROM articles WHERE id<:id",array(":id" => 3000),array("type" => "integer"));	// takto to bylo vsechno zamysleno
	*		$mole->selectSingleValue("SELECT COUNT(*) FROM articles WHERE id<:id",array(":id" => 3000),"integer");									// takto lze typ zkratit
	*		$mole->selectSingleValue("SELECT COUNT(*) FROM articles WHERE id<3000","integer");																			// a pokud se nic nebinduje, je to mozne jeste vice zkratit
	*
	* @access public
	* @param string $query
	* @param array $bind_ar			muze byt string (prevedeno bude na $options["type"])
	* @param array $options			muze byt string (prevedeno bude na $options["type"])
	* @return mixed						
	*/
	function selectSingleValue($query,$bind_ar = array(), $options = array()){
		if(is_string($bind_ar)){
			$options = array("type" => $bind_ar);
			$bind_ar = array();
		}
		if(is_string($options)){
			$options = array("type" => $options);
		}
		$ar = $this->selectFirstRow($query,$bind_ar,$options);

		if(!isset($ar) || sizeof($ar)==0){ return null; }

		$out = null;

		reset($ar);
		while(list($_key,) = each($ar)){
			$out = $ar[$_key];
			break;
		}
		if(isset($out) && isset($options["type"])){
			settype($out,"$options[type]");
		}

		return $out;
	}

	/**
	* Provede SQL dostaz a seradi vsechny hodnoty ze vsech radku do indexoveho pole.
	*
	* $article_ids = $dbmole->selectIntoArray("SELECT id FROM articles WHERE source_id=100010");
	* $article_ids -> array("233221","233222","233225"...)
	*
	* Uvedenim $options["type"] = "integer" lze docilit toho, ze vsechny existujici hodnoty budou
	* pretypovany na integer.
	*
	* $arr = $dbmole->selectIntoArray("SELECT id,name FROM articles WHERE ...");
	* $ar -> array("233221","nazev prvniho clanku","233222","nazev druheho clanku"...)
	* 
	* @access public
	* @param string $query
	* @param array $bind_ar				muze byt string (prevedeno bude na $options["type"])
	* @param array $options				muze byt string (prevedeno bude na $options["type"])
	* @return array 
	*/
	function selectIntoArray($query,$bind_ar = array(),$options = array()){
		if(is_string($bind_ar)){
			$options = array("type" => $bind_ar);
			$bind_ar = array();
		}
		if(is_string($options)){
			$options = array("type" => $options);
		}

		$out = array();

		$rows = $this->_selectRows($query,$bind_ar,$options);
		if(!is_array($rows)){ return null; }
		while(list(,$row) = each($rows)){
			reset($row);
			while(list(,$value) = each($row)){
				if(isset($value) && isset($options["type"])){
					settype($value,$options["type"]);
				}
				$out[] = $value;
			}
		}

		reset($out);
		return $out;
	}

	/**
	* $articles = $dbmole->selectIntoAssociativeArray("SELECT id,name FROM articles WHERE source_id=100010");
	* $articles ->
	*		array(
	*			"12" => "Nazev 1",
	*			"3342" => "Nazev 2",
	*			"2311" => "Nazev 3",
	*			...
	*		)
	*/
	function selectIntoAssociativeArray($query,$bind_ar = array(), $options = array()){
		$out = array();
		$ar = $this->selectIntoArray($query,$bind_ar,$options);
		for($i=0;$i<sizeof($ar);$i = $i + 2){
			$out[$ar[$i]] = $ar[$i+1];
		}
		return $out;
	}

	function begin(){
		return $this->doQuery("BEGIN");
	}

	function commit(){
		return $this->doQuery("COMMIT");
	}

	function rollback(){
		return $this->doQuery("ROLLBACK");
	}

	/**
	* $dbmole->insertIntoTable("comments",array(
	*		"title" => "Titulek",
	*		"author" => "Yarri",
	*		"body" => "text prispevku"
	*	));
	*
	* @access public
	* @param string $table_name
	* @param array $values					asociativni pole
	* @param array $options					asociativni pole
	* @return bool
	*/
	function insertIntoTable($table_name,$values,$options = array()){
		settype($table_name,"string");
		settype($values,"array");

		if(!isset($options["do_not_escape"])){ $options["do_not_escape"] = array(); } 
		if(!is_array($options["do_not_escape"])){ $options["do_not_escape"] = array($options["do_not_escape"]); }
		
		$query_fields = array();
		$query_values = array();
		$bind_ar = array();
		reset($values);
		while(list($_field_name,$_value) = each($values)){
			$query_fields[] = $_field_name;
			if(in_array($_field_name,$options["do_not_escape"])){
				$query_values[] = $_value;
				continue;
			}
			$query_values[] = ":$_field_name";
			$bind_ar[":$_field_name"] = is_object($_value) ? $_value->getId() : $_value;
		}

		return $this->doQuery("INSERT INTO $table_name (".join(",",$query_fields).") VALUES(".join(",",$query_values).")",$bind_ar,$options);
	}

	/**
	* Vlozi do tabulky zaznam, pokud jeste neexistuje. Pokud vsak existuje, bude tento zaznam updatovan.
	* 
	*		$dbmole->insertOrUpdateRecord("persons",
	*			array(
	*				"id" => 1000,
	*				"firstname" => "John",
	*				"surname" => "Blbec",
	*				"updated" => "NOW()"
	*			),
	*			array(
	*				"id_field" => "id",
	*				"do_not_escape" => array("updated")
	*			)
	*		);
	*/
	function insertOrUpdateRecord($table_name,$values,$options = array()){
		settype($table_name,"string");
		settype($values,"array");

		// nazev policka, ktere je rozhodujici, zda zaznam existuje nebo nikoli
		$options["id_field"] = isset($options["id_field"]) ? (string)$options["id_field"] : "id";
		if(!isset($options["do_not_escape"])){ $options["do_not_escape"] = array(); } 
		if(!is_array($options["do_not_escape"])){ $options["do_not_escape"] = array($options["do_not_escape"]); }

		$id_field = $options["id_field"];
		$id_value = $values[$id_field];

		unset($options["id_field"]); // dale toto nastaveni uz neni nutne

		// TODO: tady se zatim vubec neresi to, ze muze byt nastaveno $options["do_not_escape"] = array("id")
		$_options = $options;
		$_options["type"] = "integer";
		$count = $this->selectSingleValue("SELECT COUNT(*) FROM $table_name WHERE $id_field=:id_value",array(":id_value" => $id_value),$_options);

		if($count==0){

			return $this->insertIntoTable($table_name,$values,$options);

		}else{

			$update_ar = array();
			$bind_ar = array();
			reset($values);
			while(list($_key,$_value) = each($values)){
				if(!isset($options["do_not_escape"]["$_key"])){
					$bind_ar[":$_key"] = is_object($_value) ? $_value->getId() : $_value;
				}
				if($_key == $id_field){ continue; }
				if(!isset($options["do_not_escape"]["$_key"])){
					$update_ar[] = "$_key=:$_key";	
				}else{
					$update_ar[] = "$_key=$_value";
				}
			}
			if(sizeof($update_ar)==0){ return true; } // je to podivne, ale tady se nic nemeni; nekdo vola nmetodu nesmyslne ve stylu: $dbmole->insertOrUpdateRecord("persons",array("id" => 20));
			return $this->doQuery("UPDATE $table_name SET ".join(", ",$update_ar)." WHERE $id_field=:$id_field",$bind_ar,$options);

		}
	}

	function closeConnection(){
		if(!isset($this->_DbConnect)){ return; }
		$this->_disconnectFromDatabase();
		$this->_DbConnect = null;
	}

	function selectSequenceNextval($sequence_name){ return null; }
	function selectSequenceCurrval($sequence_name){ return null; }
	function usesSequencies(){ return true; }

	function executeQuery($query,$bind_ar = array(),$options = array()){
		settype($query,"string");
		settype($bind_ar,"array");
		settype($options,"array");

		$this->_initializeBeforeDoingSQL();

		$this->_Query = $query;
		$this->_BindAr = $bind_ar;
		$this->_Options = $options;

		$this->_hookBeforeQueryExecution();

		$out = $this->_executeQuery();

		$this->_hookAfterQueryExecution();

		return $out;
	}

	function escapeFloat4Sql($f){
		return (string)$f;
	}

	function escapeTableName4Sql($t){
		return $t;
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

		// bindovani promennych
		// vyuzijeme zde schopnosti fce strtr
		// pokud se napr. budou bindovat 2 hodnoty :domain a :domain_name, strtr si s tim poradi!
		reset($bind_ar);
		while(list($key,$value) = each($bind_ar)){
			if(!isset($value)){ $bind_ar[$key] = "NULL"; continue; }
			if(is_integer($value)){ continue; }
			if(is_float($value)){ continue; }
			$value = $this->escapeString4Sql($value);
			$bind_ar[$key] = $value;
		}
		$query_to_execute = strtr($query,$bind_ar);

		// 
		$result = $this->_runQuery($query_to_execute);
		if(!$result){
			$this->_raiseDBError("failed to execute SQL query");
			return null;
		}

		return $result;
	}


	
	// chybova zprava zavisla na typu databaze
	function _getDbLastErrorMessage(){ return ""; }

	function _hookBeforeQueryExecution(){
		if(defined("DBMOLE_COLLECT_STATICTICS") && DBMOLE_COLLECT_STATICTICS){
			list($usec, $sec) = explode(" ", microtime());
			$this->_start_utime = ((float)$usec + (float)$sec);
		}
	}

	function _hookAfterQueryExecution(){
		global $__DMOLE_STATISTICS__;

		if(defined("DBMOLE_COLLECT_STATICTICS") && DBMOLE_COLLECT_STATICTICS){
			if(!isset($__DMOLE_STATISTICS__)){ $__DMOLE_STATISTICS__ = array(); }
			if(!isset($__DMOLE_STATISTICS__[$this->getQuery()])){
				$__DMOLE_STATISTICS__[$this->getQuery()] = array();
			}

			$start_utime = $this->_start_utime;
			list($usec, $sec) = explode(" ", microtime());
			$stop_utime = ((float)$usec + (float)$sec);

			$__DMOLE_STATISTICS__[$this->getQuery()][] = array(
				"time" => $stop_utime - $start_utime,
				"bind_ar" => $this->getBindAr()
			);
		}

		//echo "<pre>";
		//echo $this->getQuery();
		//echo "</pre>";

		//echo $stop_utime - $start_utime;
		//echo " -> ";
		//echo $this->total_time;
		//echo "<br>";
	}

	function _readCache($seconds,$query,$bind_ar,$options){
		$filename = $this->_getCacheFilename($query,$bind_ar,$options);
		if(!file_exists($filename) || filemtime($filename)<(time()-$seconds)){
			return null;
		}
		$cache = files::get_file_content($filename,$error,$error_str);
		$rows = unserialize($cache);
		if(!is_array($rows)){
			return null;
		}
		return $rows;
	}

	function _writeCache(&$rows,$query,$bind_ar,$options){
		$cache = serialize($rows);
		$filename = $this->_getCacheFilename($query,$bind_ar,$options);
		$dir = preg_replace("/[^\\/]*$/","",$filename);
		files::mkdir($dir,$error,$error_str);
		files::write_to_file($filename,$cache,$error,$error_str);
		return true;
	}

	function _getCacheFilename($query,$bind_ar,$options){
		return $this->_CacheDir."/".md5($query)."/".md5(
			serialize(array(
				"bind_ar" => $bind_ar,
				"options" => $options
			))
		);
	}

}
?>

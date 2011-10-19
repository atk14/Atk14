<?php
/**
 * Provides basic functionality with databases.
 *
 * This class provides methods that are independent on the database type.
 * Database dependent methods are overridden in descendants
 *
 *
 * Error handling
 * <code>
 *	DbMole::RegisterErrorHandler("dbmole_error_handler");
 *	function dbmole_error_handler($dbmole){
 *		echo "drahy uzivateli, bohuzel doslo k chybe";
 *		$dbmole->sendErrorReportToEmail("admin@test.cz");
 *		$dbmole->logErrorReport();
 *		exit;
 *	}
 * </code>
 *
 * Statistics:
 * <code>
 *	define("DBMOLE_COLLECT_STATICTICS",true);
 *	$dbmole = &OracleMole::GetInstance();
 *	echo $dbmole->getStatistics();
 * </code>
 * @package Atk14
 * @subpackage InternalLibraries
 * @abstract
 * @filesource
 */

/**
 * Basic database functionality.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @abstract
 * @filesource
 */
class DbMole{
	/**
	 * @access private
	 */
	var $_ConfigurationName = "";

	/**
	 * @access private
	 */
	var $_DbConnect = null;


	/**
	 * @access private
	 */
	var $_ErrorMessage = null;

	/**
	 * @access private
	 * @internal Is this attribute really used?
	 * @todo check var usage
	 */
	var $_ErrorHangler = null;


	/**
	 * @access private
	 */
	var $_Query = "";

	/**
	 * @access private
	 */
	var $_BindAr = array();

	/**
	 * @access private
	 */
	var $_Options = array();

	/**
	 * @access private
	 */
	var $_QueriesExecuted = 0;


	/**
	 * @access private
	 */
	var $_CacheDir = null;

	/**
	 * Returns an instance of DB connector.
	 *
	 * Returns an instance of DB connector for given configuration. The object is always the same for given configuration.
	 *
	 * Basic call:
	 * <code>
	 * $dbmole = &DbMole::GetInstance("default","OracleMole");
	 * </code>
	 *
	 * This call using a descendant is better:
	 * <code>
	 * $dbmole = &OracleMole::GetInstance("default");
	 * </code>
	 *
	 * @param string $configuration_name
	 * @param string $class_name
	 * @return DbMole
	 */
	static function &GetInstance($configuration_name,$class_name){
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
	* Returns the database name.
	*
	* If a descendant doesn't know how to determine the database name,
	* the configuration name will be returned instead.
	*
	* @return string
	*/
	function getDatabaseName(){
		if($dbname = $this->_getDatabaseName()){ return (string)$dbname; }
		return $this->_ConfigurationName;
	}

	/**
	 * @access private
	 */
	function _getDatabaseName(){ }

	/**
	 * Registers name of function that will be called whenever execution of SQL command fails.
	 * Instance of a DbMole object (or its descendant) will be passed to the function as parameter.
	 *
	 * Returns name of error handler registered with the last call of this method.
	 *
	 * Registration of an error handler
	 * <code>
	 * DbMole::RegisterErrorHandler("dbmole_error_handler");
	 * </code>
	 *
	 * Common handler example
	 * <code>
	 * function dbmole_error_handler($dbmole){
	 *   echo "drahy uzivateli, bohuzel doslo k chybe";
	 *	 $dbmole->sendErrorReportToEmail("admin@test.cz");
	 *	 $dbmole->logErrorReport();
	 *	 exit;
	 * }	
	 * </code>
	 *
	 * @param string $function_name
	 * @return string name of previously registered error handler
	 */
	static function RegisterErrorHandler($function_name){
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

	/**
	 * @access private
	 */
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
	static function _GetSetErrorHandlerFunction($function_name = "",$set = false){
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
	 * Connects the database.
	 *
	 * This method is abstract.
	 * Musi byt prekryto!
	 *
	 * @access private
	 * @abstract
	 * @return bool true -> uspesne napojeno, false -> doslo k chybe
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

	/**
	 * @access private
	 */
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
	 * Checks if an error occured on last query.
	 *
	 * Some methods return values say that error occured (commit(), rollback(), selectRows(), ...).
	 * From return values of other methods it is impossible to recognize an error (selectSingleValue(), selectFirstRow(), ...).
	 *
	 * When you register an error handler that interrupts the script (with exit) then it is not needed to check for an error.
	 *
	 * @return bool true -> error occured, false -> no error
	 */
	function errorOccurred(){ return isset($this->_ErrorMessage); }

	/**
	* Gettery vhodne pro error_handler funkci.
	*/
	function getErrorMessage(){ return $this->_ErrorMessage; }
	/**
	 * @return string
	 */
	function getQuery(){ return $this->_Query; }

	/**
	 * @return array
	 */
	function getBindAr(){ return $this->_BindAr; }

	/**
	 * @return array
	 */
	function getOptions(){ return $this->_Options; }

	function getQueriesExecuted(){ return $this->_QueriesExecuted; }

	/**
	 * @return string
	 */
	function getConfigurationName(){ return $this->_ConfigurationName; }

	/**
	 * @return string
	 */
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


	/**
	 * @access private
	 */
	function _initializeBeforeDoingSQL(){
		$this->_reset();
		$this->_connectToDatabase();
	}

	/**
	 * Resets some states of objects.
	 *
	 * Is called before execution of a SQL command.
	 *
	 * @access private
	 *
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
	 * This method is called in case of error.
	 *
	 * @access private
	 * @param string $message_prefix				ie. "OCIParse failed"
	 *
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
	 * Executes SQL query.
	 *
	 * @param string $query
	 * @param array $bind_ar
	 * @param array $options
	 * @return bool			true -> query executed with success
	 *									false -> error
	 */
	function doQuery($query,$bind_ar = array(), $options = array()){
		$result = $this->executeQuery($query,$bind_ar,$options);
		if(!$result){ return false; }
		$this->_freeResult($result);
		return true;
	}

	/**
	 * Returns first record as associative array.
	 *
	 * Returns null when result doesn't contain any record or an error occurs.
	 *
	 * @param string $query
	 * @param array $bind_ar
	 * @param array $options
	 * @return array associative array
	 */
	function selectFirstRow($query,$bind_ar = array(), $options = array()){
		$records = $this->_selectRows($query,$bind_ar,$options);
		if(!isset($records) || sizeof($records)==0){
			return null;
		}
		return $records[0];
	}
	
	/**
	 * Returns first value from the first record.
	 *
	 * Useful method for queries that count something like this:
	 * 'SELECT COUNT(*) AS count FROM articles WHERE source_date>SYSDATE'
	 *
	 * When the value is NULL this method returns null
	 *
	 *
	 * Options can specify type of returned value. Just use 'type' => "integer" option.
	 *
	 * As this is used a lot, this option can be passed directly without the 'type' keyword.
	 * It is internally converted to array.
	 *
	 *
	 * Basic usage
	 * <code>
	 * $mole->selectSingleValue("SELECT COUNT(*) FROM articles WHERE id<:id",array(":id" => 3000),array("type" => "integer"));	// takto to bylo vsechno zamysleno
	 * </code>
	 *
	 * can be shortened:
	 * <code>
	 * $mole->selectSingleValue("SELECT COUNT(*) FROM articles WHERE id<:id",array(":id" => 3000),"integer");
	 * </code>
	 *
	 * and can be even more shortened when no bind_ar is passed
	 * <code>
	 * $mole->selectSingleValue("SELECT COUNT(*) FROM articles WHERE id<3000","integer");
	 * </code>
	 *
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
	 * Shortcut to getting an integer.
	 */
	function selectInt($query,$bind_ar = array(),$options = array()){
		$options["type"] = "integer";
		return $this->selectSingleValue($query,$bind_ar,$options);
	}

	/**
	 * Shortcut to getting an string.
	 */
	function selectString($query,$bind_ar = array(),$options = array()){
		$options["type"] = "string";
		return $this->selectSingleValue($query,$bind_ar,$options);
	}

	/**
	 * Shortcut to getting an float.
	 */
	function selectFloat($query,$bind_ar = array(),$options = array()){
		$options["type"] = "float";
		return $this->selectSingleValue($query,$bind_ar,$options);
	}

	/**
	 * Shortcut to getting a boolean.
	 * Values considered as the true are: 'y', 'yes', 't', 'true', '1'...
	 */
	function selectBool($query,$bind_ar = array(),$options = array()){
		$value = $this->selectString($query,$bind_ar,$options);
		if(!isset($value)){ return null; }
		return
			in_array(strtoupper($value),array("Y","YES","YUP","T","TRUE","1","ON","E","ENABLE","ENABLED")) ||
			(is_numeric($value) && $value>0);
	}

	/**
	 * Executes a SQL query and packs all values from all records to an array.
	 *
	 * Setting option type to 'integer' causes that all returned values are converted to integers.
	 *
	 * <code>
	 * $article_ids = $dbmole->selectIntoArray("SELECT id FROM articles WHERE source_id=100010");
	 * </code>
	 * returned array $article_ids can be array("233221","233222","233225"...)
	 *
	 * <code>
	 * $arr = $dbmole->selectIntoArray("SELECT id,name FROM articles WHERE ...");
	 * </code>
	 * returns array $ar like this
	 * array("233221","nazev prvniho clanku","233222","nazev druheho clanku"...)
	 *
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
	 * Returns records as associative arrays with the first attributes value as key.
	 *
	 * sql specifies 2 fields:
	 * <code>
	 * $articles = $dbmole->selectIntoAssociativeArray("SELECT id,name FROM articles WHERE source_id=100010");
	 * </code>
	 * can return for example
	 *	array(
	 *		"12" => "Nazev 1",
	 *		"3342" => "Nazev 2",
	 *		"2311" => "Nazev 3",
	 *		...
	 *	)
	 *
	 * sql specifies more fields and this call
	 * <code>
	 * $articles = $dbmole->selectIntoAssociativeArray("SELECT id,name,author FROM articles WHERE source_id=100010");
	 * </code>
	 * can return this
	 *	array(
	 *		"12" => array("name" => "Nazev 1", "author" => "Jan Tuna"),
	 *		"3342" => array("name" => "Nazev 2", "author" => "Dr. Kanal"),
	 *		...
	 *	)
	 *
	 * @param string $query
	 * @param array $bind_ar
	 * @param array $options
	 * @return array
	 */
	function selectIntoAssociativeArray($query,$bind_ar = array(), $options = array()){
		$out = array();
		$rows = $this->selectRows($query,$bind_ar,$options);
		foreach($rows as $row){
			$keys = array_keys($row);
			if(sizeof($keys)==2){
				$out[$row[$keys[0]]] = $row[$keys[1]];
			}else{
				$k = $row[$keys[0]];
				unset($row[$keys[0]]);
				$out[$k] = $row; 
			}
		}
		return $out;
	}

	/**
	 * Starts a database transaction.
	 *
	 * @return bool
	 */
	function begin(){
		return $this->doQuery("BEGIN");
	}

	/**
	 * Ends a database transaction.
	 *
	 * @return bool
	 */
	function commit(){
		return $this->doQuery("COMMIT");
	}

	/**
	 * Rollbacks all database operations.
	 * @return bool
	 */
	function rollback(){
		return $this->doQuery("ROLLBACK");
	}

	/**
	 * Inserts a record into database table.
	 *
	 * Takes an associative array of column => value pairs and creates a new record with those values in given table.
	 *
	 * <code>
	 * $dbmole->insertIntoTable("comments",array(
	 *		"title" => "Titulek",
	 *		"author" => "Yarri",
	 *		"body" => "text prispevku"
	 *	));
	 * </code>
	 *
	 * @param string $table_name
	 * @param array $values		associative array
	 * @param array $options	associative array
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
	 * Inserts a record into a table or updates a record if it already exists.
	 *
	 * <code>
	 *	$dbmole->insertOrUpdateRecord("persons",
	 *		array(
	 *			"id" => 1000,
	 *			"firstname" => "John",
	 *			"surname" => "Blbec",
	 *			"updated" => "NOW()"
	 *		),
	 *		array(
	 *			"id_field" => "id",
	 *			"do_not_escape" => array("updated")
	 *		)
	 *	);
	 * </code>
	 *
	 * @return bool
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

	/**
	 * Disconnects database
	 */
	function closeConnection(){
		if(!isset($this->_DbConnect)){ return; }
		$this->_disconnectFromDatabase();
		$this->_DbConnect = null;
	}

	function selectSequenceNextval($sequence_name){ return null; }
	function selectSequenceCurrval($sequence_name){ return null; }
	function usesSequencies(){ return true; }

	/**
	 * Executes a query.
	 *
	 * To prevent against a SQL attack you should not write conditions directly to query string but you should use the form with $bind_ar to sanitize the input data.
	 *
	 * <code>
	 * $dbmole->executeQuery("SELECT * FROM articles WHERE id=:id",array(":id" => 123));
	 * </code>
	 *
	 * Also arrays can be used as bind_ar
	 * <code>
	 * $dbmole->executeQuery("SELECT * FROM articles WHERE id IN :ids",array(":ids" => array(123,124,125)));
	 * </code>
	 * which will be internally transformed into this
	 * <code>
	 * $dbmole->executeQuery("SELECT * FROM articles WHERE id IN (:ids_0, :ids_1, :ids_2)",array(":ids_0" => 123, ":ids_1" => 124, ":ids_2" => 125));
	 * </code>
	 *
	 * In $options array the execution mode can be set:
	 *		$options["mode"] = OCI_DEFAULT
	 *		$options["mode"] = OCI_COMMIT_ON_SUCCESS
	 * Default mode is OCI_DEFAULT.
	 *
	 * @param string $query
	 * @param array $bind_ar
	 * @param array $options
	 * @return statement or null on error
	 */
	function executeQuery($query,$bind_ar = array(),$options = array()){
		settype($query,"string");
		settype($bind_ar,"array");
		settype($options,"array");

		// prevod prip. poli v $bind_ar
		$b_ar = array();
		foreach($bind_ar as $key => $value){
			if(is_array($value)){
				$replace = array();
				foreach($value as $_k => $_v){
					$b_ar["{$key}_$_k"] = $_v;
					$replace[] = "{$key}_$_k";
				}
				$query = str_replace($key,"(".join(", ",$replace).")",$query);
				continue;
			}
			$b_ar[$key] = $value;
		}
		$bind_ar = $b_ar;

		$this->_initializeBeforeDoingSQL();

		$this->_Query = $query;
		$this->_BindAr = $bind_ar;
		$this->_Options = $options;

		$this->_hookBeforeQueryExecution();

		$out = $this->_executeQuery();

		$this->_QueriesExecuted++;

		$this->_hookAfterQueryExecution();

		return $out;
	}

	/**
	 *
	 * @return string
	 */
	function escapeFloat4Sql($f){
		return (string)$f;
	}

	/**
	 * @return string
	 */
	function escapeTableName4Sql($t){
		return $t;
	}

	/**
	 * Realizes the query execution.
	 *
	 * @access private
	 * @return statement or null on error
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
			if(is_object($value)){ $value = $value->getId(); }
			if(!is_integer($value) && !is_float($value)){
				$value = $this->escapeString4Sql($value);
			}
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


	
	/**
	 * error message dependent on database type
	 *
	 * @access private
	 */
	function _getDbLastErrorMessage(){ return ""; }

	/**
	 * @access private
	 */
	function _hookBeforeQueryExecution(){
		if(defined("DBMOLE_COLLECT_STATICTICS") && DBMOLE_COLLECT_STATICTICS){
			list($usec, $sec) = explode(" ", microtime());
			$this->_start_utime = ((float)$usec + (float)$sec);
		}
	}

	/**
	 * @access private
	 */
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

	/**
	 * @access private
	 */
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

	/**
	 * @access private
	 */
	function _writeCache(&$rows,$query,$bind_ar,$options){
		$cache = serialize($rows);
		$filename = $this->_getCacheFilename($query,$bind_ar,$options);
		$dir = preg_replace("/[^\\/]*$/","",$filename);
		files::mkdir($dir,$error,$error_str);
		files::write_to_file($filename,$cache,$error,$error_str);
		return true;
	}

	/**
	 * @access private
	 */
	function _getCacheFilename($query,$bind_ar,$options){
		return $this->_CacheDir."/".md5($query)."/".md5(
			serialize(array(
				"bind_ar" => $bind_ar,
				"options" => $options
			))
		);
	}

}

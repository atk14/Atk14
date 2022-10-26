<?php
/**
 * Class for finding records.
 *
 * @filesource
 *
 */

/**
 * Class for finding records.
 *
 * Initialization of Finder:
 * ```
 * $finder = TableRecord::Finder(array(
 * 	"class_name" => "Books",
 * 	"conditions" => array("title LIKE :title"),
 * 	"bind_ar" => array(":title" => "%British%"),
 * 	"limit" => 10,
 * 	"offset" => 0,
  *	));
 * ```
 *
 * This call returns total number of found records:
 * ```
 * $finder->getRecordsCount();
 * ```
 *
 * Get records. Number of records is limited by option limit
 * ```
 * $finder->getRecords(); // pole objektu, max velikost je omezena nastavenim "limit"
 * ```
 *
 * @package Atk14\TableRecord
 */
class TableRecord_Finder implements ArrayAccess, Iterator, Countable {

	protected $associative = null;

	/**
	 * Constructor
	 *
	 * @param array $options
	 * <ul>
	 * 	<li><b>query</b> - custom query</li>
	 * 	<li><b>query_count</b> - custom query for counting records</li>
	 * 	<li><b>bind_ar</b> - array of binding parameters</li>
	 * 	<li><b>class_name</b> - specifies class of returned objects</li>
	 * 	<li><b>options</b></li>
	 * </ul>
	 * @param DbMole $dbmole
	 *
	 */
	function __construct($options,&$dbmole){
		$options += array(
			"query" => null,
			"query_count" => null,

			"bind_ar" => array(),
			"options" => array(),
			"class_name" => "",

			"use_cache" => TABLERECORD_USE_CACHE_BY_DEFAULT,
		);
		$this->_Query = $options["query"];
		$this->_QueryCount = $options["query_count"];
		$this->_BindAr = $options["bind_ar"];
		$this->_QueryOptions = $options["options"];
		$this->_ClassName = $options["class_name"];
		$this->_UseCache = $options["use_cache"];

		$this->_dbmole = &$dbmole;
	}

	/**
	 * Gets found records.
	 *
	 * Returns array of records of specified class.
	 *
	 * @return array array of records
	 */
	function getRecords(){
		if(!isset($this->_Records)){
			if($this->_UseCache){
				$this->_Records = Cache::Get($this->_ClassName,$this->getRecordIds());
			}else{
				$this->_Records = call_user_func_array(array($this->_ClassName,"GetInstanceById"),array($this->getRecordIds()));
			}
		}
		return $this->_Records;
	}

	/**
	 * Gets ids of found records.
	 *
	 * @return array array of found ids
	 */
	function getRecordIds() {
		if(!isset($this->_RecordIds)){
			$this->_RecordData = $this->_dbmole->selectRows($this->_Query,$this->_BindAr,$this->_QueryOptions);
			if( $this->_RecordData ) {
				$this->_RecordKey = key(current($this->_RecordData));
				$this->_RecordIds = array_column($this->_RecordData, $this->_RecordKey);
			} else {
				$this->_RecordIds = array();
			}
		}
		return $this->_RecordIds;
	}

	/**
	 * Returns other data selected by query given to Finder to select ids.
	 *
	 * First field of the query should return ids of selected records.
	 *
	 * @return array or mixed: field or all fields associated with current record, or with
	 *                         all records if no params are given.
	 *
	 * ```
	 * $finder = User::Finder(["query" => "SELECT id, some, other, data FROM users"]);
	 *
	 * $data = $finder->getQueryData(); // all the query data
	 *
	 * foreach($finder as $user) {
	 *     echo $finder->getQueryData($user,'some');   // just one field
	 *     echo $finder->getQueryData($user)['other']; // array of data of given users
	 *     echo $finder->getQueryData()[$user->getId()]['data'];  // whole dataset (associative array)
	 * }
	 * ```
	 */
	function getQueryData($id = null, $field = null) {
		$this->getRecordIds();
		if(!$this->associative) {
			foreach($this->_RecordData as $row) {
				$aid = $row[$this->_RecordKey];
				$this->associative[$aid] = $row;
			}
		}
		if($id !== null) {
			if(is_object($id)) {
				$id = $id->getId();
			}
			if($field) {
				return $this->associative[$id][$field];
			}
			return $this->associative[$id];
		} else {
			return $this->associative;
		}
	}

	/**
	 * Returns records count in the current result window specified by limit and offset.
	 * 
	 */
	function getRecordsDisplayed(){
		return sizeof($this->getRecords());
	}

	/**
	 * Gets total amount of found records.
	 *
	 * @return integer
	 */
	function getRecordsCount(){
		if(!isset($this->_RecordsCount)){
			$options = $this->_QueryOptions;
			if(
				isset($this->_Records) &&
				$options["offset"] == 0 &&
				(count($this->_Records) < $options["limit"] || !$options["limit"])
			){
				return $this->_RecordsCount = count($this->_Records);
			}
			unset($options["limit"]);
			unset($options["offset"]);
			$options["type"] = "integer";
			$this->_RecordsCount = $this->_dbmole->selectSingleValue($this->_QueryCount,$this->_BindAr,$options);
		}
		return $this->_RecordsCount;
	}

	/**
	 * Gets total amount of found records.
	 *
	 * Alias to method {@link getRecordsCount()}.
	 *
	 * @return integer
	 * @uses getRecordsCount()
	 */
	function getTotalAmount(){
		return $this->getRecordsCount();
	}

	/**
	 * Checks if the returned recordset was empty.
	 *
	 * @return bool true when no records were found otherwise false
	 */
	function isEmpty(){ return $this->getTotalAmount()==0; }

	/**
	 * Checks if the returned recordset was not empty.
	 *
	 * Returns negative value of method {@link isEmpty()}
	 *
	 * @return bool false when no records were found otherwise true
	 */
	function notEmpty(){ return !$this->isEmpty(); }

	/**
	 * Getter for limit option
	 *
	 * @return integer
	 */
	function getLimit(){
		return $this->_QueryOptions["limit"];
	}

	/**
	 * Getter for offset option.
	 *
	 * @return integer
	 */
	function getOffset(){
		return $this->_QueryOptions["offset"];
	}

	/**
	 * if(!$finder->atBeginning()){
	 *	// display prev records link
	 * }
	 */
	function atBeginning(){
		return $this->getOffset()<=0;
	}

	/**
	 * if($function->atEnd()){
	 *	echo "there are no more records";
	 * }
	 */
	function atEnd(){
		if($this->getRecordsCount()==0){ return true; }
		return ($this->getOffset() + $this->getRecordsDisplayed())>=$this->getTotalAmount();
	}

	/**
	 * 
	 */
	function getNextOffset(){
		$next_offset = $this->getOffset() + $this->getLimit();
		return $next_offset>($this->getTotalAmount()-1) ? null : $next_offset;
	}

	function getPrevOffset(){
		$prev_offset = $this->getOffset() - $this->getLimit();
		return $prev_offset<=0 ? null : $prev_offset;
	}

	/*** functions implementing array like access ***/
	#[\ReturnTypeWillChange]
	function offsetGet($value){
		$x=$this->getRecords();
		return $x[$value];
	}

	function offsetSet($value, $name): void{
		$this->getRecords();
		$this->_Records[$name]=$value;
	}

	function offsetUnset($value):void {
		$this->getRecords();
		unset($this->_Records[$name]);
	}

	#[\ReturnTypeWillChange]
	function offsetExists($value):bool {
		$this->getRecords();
		return array_key_exists($name, $this->_Records);
	}

	/*** functions implementing iterator like access (foreach cycle)***/
	#[\ReturnTypeWillChange]
	public function current(){
		return current($this->_Records);
	}

	#[\ReturnTypeWillChange]
	public function key(){
		return key($this->_Records);
	}
	public function next():void {
		next($this->_Records);
	}
  public function rewind():void {
   $this->getRecords();
	 reset($this->_Records);
	}
	public function valid():bool {
		return isset($this->_Records) && current($this->_Records);
	}

	#[\ReturnTypeWillChange]
	public function count(){
		return $this->getRecordsDisplayed();
	}
}

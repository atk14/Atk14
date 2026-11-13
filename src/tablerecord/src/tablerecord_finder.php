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
 * $finder->getRecords(); // array of objects, maximum size is limited by the "limit" option
 * ```
 *
 * @package Atk14\TableRecord
 */
class TableRecord_Finder implements ArrayAccess, Iterator, Countable {

	protected $associative = null;
	protected $_Query;
	protected $_QueryCount;
	protected $_BindAr;
	protected $_QueryOptions;
	protected $_ClassName;
	protected $_UseCache;
	protected $_dbmole;
	protected $_Records;
	protected $_RecordsCount;
	protected $_RecordIds;
	protected $_RecordKey;
	protected $_RecordData;

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
		$options += [
			"query" => null,
			"query_count" => null,

			"bind_ar" => [],
			"options" => [],
			"class_name" => "",

			"use_cache" => TABLERECORD_USE_CACHE_BY_DEFAULT,
		];
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
			$this->_Records = $this->_getRecordsByIds($this->getRecordIds());
		}
		return $this->_Records;
	}

	/**
	 * Iterates over found records without loading them all into memory at once.
	 *
	 * Unlike {@link getRecords()}, which reads every matching id and every corresponding
	 * record in one go, iterateRecords() streams ids from the database one by one and only
	 * ever keeps one batch of records (see the <b>batch_size</b> option) in memory at a time.
	 * When the finder uses the cache (the default), each batch is read via
	 * {@link Cache::Prepare()}/{@link Cache::Get()} and evicted again via {@link Cache::Clear()}
	 * right after being yielded -- see {@link _iterateRecordsByIds()} for why this method first
	 * has to reclaim the prepare-queue for this class via {@link Cache::Unprepare()}.
	 * This is useful when a finder is set up to match (and process) a large number of records,
	 * e.g. with a high or no limit at all.
	 *
	 * ```
	 * $finder = Book::Finder(["limit" => null]); // no limit -> finds all the records
	 * foreach($finder->iterateRecords() as $book){
	 * 	// $book is read from the database without all the other books being loaded into memory
	 * }
	 * ```
	 *
	 * @param array $options
	 * <ul>
	 * 	<li><b>batch_size</b> - how many records are kept resident (and, when the finder uses
	 * 	the cache, identity-mapped) at a time (default: TABLERECORD_MAX_NUMBER_OF_RECORDS_READ_AT_ONCE).
	 * 	This is a memory/query-count tradeoff, not a hard cap on a single query's size (both the
	 * 	cache and non-cache paths already split any individual read into
	 * 	TABLERECORD_MAX_NUMBER_OF_RECORDS_READ_AT_ONCE-sized SQL queries regardless of batch_size).
	 * 	A bigger batch_size keeps more records resident simultaneously, so code that cross-references
	 * 	other records of the same class while processing one (e.g. a related/accessory lookup)
	 * 	is more likely to find them already cached instead of triggering an extra query -- at the
	 * 	cost of proportionally higher peak memory. How much that matters depends entirely on how
	 * 	much such cross-referencing the caller's own processing actually does.</li>
	 * </ul>
	 * @return \Generator yielding found records
	 */
	function iterateRecords($options = []){
		$options += [
			"batch_size" => TABLERECORD_MAX_NUMBER_OF_RECORDS_READ_AT_ONCE,
		];
		$batch_size = max(1,(int)$options["batch_size"]);

		if($this->_UseCache){
			// TableRecord::_Finder() already called Cache::Prepare() with *every* id matched by
			// this finder (not just the ids of the first batch) -- a side effect of _Finder()'s
			// own getRecords()-oriented optimization, unrelated to how iterateRecords() wants to
			// batch things. If left in place, the first Cache::Get() call below would have
			// ObjectCacher::_readToCache() drain a big, uncontrolled chunk of that queue -- not
			// just the current batch -- on every single call, defeating the point of iterating
			// in bounded batches. Cache::Unprepare() cancels just that still-unread queue (no
			// need to know which ids are even in it) and leaves any already resolved -- and
			// possibly unrelated -- cached objects of this class alone; this method refills the
			// queue one batch at a time itself from here on.
			Cache::Unprepare($this->_ClassName);
		}

		$ids = [];
		foreach($this->_streamIds() as $id){
			$ids[] = $id;

			if(count($ids)>=$batch_size){
				foreach($this->_iterateRecordsByIds($ids) as $record){
					yield $record;
				}
				$ids = [];
			}
		}

		if($ids){
			foreach($this->_iterateRecordsByIds($ids) as $record){
				yield $record;
			}
		}
	}

	/**
	 * Yields the ids matched by this finder, one by one.
	 *
	 * If getRecordIds() has already been called -- which is always the case when the finder
	 * uses the cache, see TableRecord::_Finder() -- this just reuses that already-fetched
	 * array instead of running the very same id-query a second time. Otherwise, it streams
	 * ids straight from the database via DbMole::iterateRows(), without ever materializing
	 * the whole matched id list.
	 *
	 * @access private
	 */
	function _streamIds(){
		if(isset($this->_RecordIds)){
			foreach($this->_RecordIds as $id){
				yield $id;
			}
			return;
		}

		$rows = $this->_dbmole->iterateRows($this->_Query,$this->_BindAr,$this->_QueryOptions);
		if(!$rows){ return; }

		foreach($rows as $row){
			if(!isset($this->_RecordKey)){
				$this->_RecordKey = key($row);
			}
			yield $row[$this->_RecordKey];
		}
	}

	/**
	 * Yields records one by one for the given window of ids.
	 *
	 * When the finder uses the cache, the batch is read via Cache::Prepare()/Cache::Get() (so
	 * the whole batch is fetched in one query, on the first Cache::Get() call) and evicted again
	 * via Cache::Clear() once the whole batch has been yielded, so that iterating over a large
	 * number of records doesn't keep growing the cache indefinitely. This relies on
	 * iterateRecords() having already reclaimed the prepare-queue for this class (via
	 * Cache::Unprepare()) -- otherwise ObjectCacher::_readToCache() would read far more than just
	 * this batch on every single Cache::Get() call.
	 *
	 * @access private
	 */
	function _iterateRecordsByIds($ids){
		if(!$this->_UseCache){
			$records = $this->_ClassName::GetInstanceById($ids,["use_cache" => false]);
			foreach($records as $record){
				if($record!==null){
					yield $record;
				}
			}
			return;
		}

		Cache::Prepare($this->_ClassName,$ids);
		foreach($ids as $id){
			yield Cache::Get($this->_ClassName,$id);
		}
		Cache::Clear($this->_ClassName,$ids);
	}

	/**
	 * @access private
	 */
	function _getRecordsByIds($ids){
		if($this->_UseCache){
			return Cache::Get($this->_ClassName,$ids);
		}
		return $this->_ClassName::GetInstanceById($ids);
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
				$this->_RecordIds = [];
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
			$this->associative = [];
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
		return count($this->getRecords());
	}

	/**
	 * Gets total amount of found records.
	 *
	 * @return integer
	 */
	function getRecordsCount(){
		if(!isset($this->_RecordsCount)){
			$options = $this->_QueryOptions;
			$offset = isset($options["offset"]) ? $options["offset"] : 0;
			$limit = isset($options["limit"]) ? $options["limit"] : null;

			if(
				isset($this->_Records) &&
				$offset == 0 &&
				(count($this->_Records) < $limit || !$limit)
			){
				return $this->_RecordsCount = count($this->_Records);
			}
			unset($options["limit"]);
			unset($options["offset"]);
			$options["type"] = "integer";
			$this->_RecordsCount = (int)$this->_dbmole->selectSingleValue($this->_QueryCount,$this->_BindAr,$options);
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

	function hardLimitReached(){
		return false;
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
	 * In the future, the limit could be higher than page size - i.e. the result can shows more pages
	 *
	 */
	function getPageSize(){
		return $this->getLimit();
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
	function offsetGet($offset){
		$records = $this->getRecords();
		return(isset($records[$offset])) ? $records[$offset] : null;
	}

	function offsetSet($offset, $value):void {
		$this->getRecords();
		$this->_Records[$offset] = $value;
	}

	function offsetUnset($offset):void {
		$this->getRecords();
		unset($this->_Records[$offset]);
	}

	#[\ReturnTypeWillChange]
	function offsetExists($offset):bool {
		$this->getRecords();
		return array_key_exists($offset, $this->_Records);
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
		return isset($this->_Records) && current($this->_Records) !== false;
	}

	#[\ReturnTypeWillChange]
	public function count(){
		return $this->getRecordsDisplayed();
	}
}

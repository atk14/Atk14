<?php
/**
 * Class for managing sortable records.
 *
 * @filesource
 */

/**
 * Class for managing sortable *NON-UNIQUE* lists of associated TableRecord instances.
 *
 * This class is intended for use on tables with association table (M:N model association).
 *
 * items == records from association table.
 * Item (TableRecord_ListerItem) contains info about the position of a TableRecord object in the list.
 * Position is defined by default in field 'rank'. Its name can be changed by option 'rank_field_name'.
 * Each item points to associated TableRecord record.
 *
 * ```
 * $article = Article::GetInstanceById(1);
 * $lister = $article->getLister("Authors");
 * $lister->append($author1);
 * $lister->append($author2);
 * $lister->getRecords(); // array($author1,$author2);
 * $lister->contains($author1); // true
 * $lister->contains($author3); // false
 * $items = $lister->getItems();
 * $items[0]->getRecord(); // $author1
 * $items[1]->getRecord(); // $author2
 *
 * $items[0]->getRank(); // 0
 * $items[1]->setRank(0); //
 * $items[0]->getRank(); // 1
 *
 * $lister->setRecordRank($author2,0);
 * ```
 * @package Atk14\TableRecord
 */
class TableRecord_Lister implements ArrayAccess, Iterator, Countable {

	static protected $CACHE = array();
	static protected $PREPARE = array();

	protected $iterator_offset = 0;

	/**
	 * Constructor
	 *
	 * This class uses association table which is derived from the table name of the owning table and from the table name of the associated records.
	 * Used table names are derived from the name of the owner class and the name of the subjects.
	 * For example, we use model Articles for articles and each article (in table articles) has some authors (in table authors).
	 * Based on this information the TableRecord_Lister assumes that the associating table will have the name articles_authors.
	 * In that table it uses foreign keys article_id and author_id which lead to appropriate ids in tables articles and authors.
	 * Field name that controls position in the collection is named 'rank' by default.
	 *
	 * All the used element names can be changed by options.
	 *
	 * Corresponding example
	 * ```
	 * $authors_lister = new TableRecord_Lister($article,"Authors",array(
	 * ));
	 * ```
	 *
	 * Description $options:
	 * - class_name
	 * - table_name - name of table used as association table
	 * - id_field_name - name of the field used as primary key of the association table
	 * - owner_field_name - name of the foreign key to connect associating table and owning table
	 * - subject_field_name - name of the foreign key used to connect associating table and subjects table
	 * - rank_field_name - name of the field used for sorting
	 *
	 * @param TableRecord $owner TableRecord instance to which the $subjects are associated
	 * @param string $subjects name of associated table is derived from subjects
	 * @param array $options
	 *
	 * @todo comment options
	 */
	function __construct($owner,$subjects,$options = array()){
		$owner_class = new String4(get_class($owner));
		$owner_class_us = $owner_class->underscore();
		$subjects = new String4($subjects);
		$subjects_us = $subjects->underscore();
		$subject = $subjects->singularize();
		$subject_us = $subject->underscore();

		$options = array_merge(array(
			"class_name" => "$subject", // Author
			"table_name" => "{$owner_class_us}_{$subjects_us}", // article_authors
			"id_field_name" => "id",
			"owner_field_name" => "{$owner_class_us}_id", // article_id
			"subject_field_name" => "{$subject_us}_id", // author_id
			"rank_field_name" => "rank",
		),$options);

		$options = array_merge(array(
			"sequence_name" => "seq_$options[table_name]"
		),$options);

		$this->_owner = &$owner;
		$this->_dbmole = &$owner->dbmole;
		$this->_options = $options;
	}

	/**
	 * Prefetches data for given set of objects
	 *
	 * It helps to lower database usage.
	 *
	 * Usage:
	 * ```
	 * $lister = $article1->getLister("Authors");
	 * $lister->prefetchDataFor($article2);
	 * $lister->prefetchDataFor(array($article3,$article4));
	 * ```
	 *
	 * Explanation:
	 * ```
	 * $lister = $article1->getLister("Authors");
	 * $lister->prefetchDataFor(array($article2,$article3));
	 * $authors = $lister->getRecords(); // data are being read for $article1, $article2 and $article3
	 * ```
	 *
	 * and then there is no need to read data
	 * ```
	 * $authors2 = $article2->getLister("Authors")->getRecords();
	 * $authors3 = $article3->getLister("Authors")->getRecords();
	 * ```
	 */
	function prefetchDataFor($owners,$options = array()){
		$options += array(
			"force_read" => false,
		);
		if(!is_array($owners)){ $owners = array($owners); }
		$owners = TableRecord::ObjToId($owners);
		$c_key = $this->_getCacheKey();

		if(!isset(self::$CACHE[$c_key])){ self::$CACHE[$c_key] = array(); }
		if(!isset(self::$PREPARE[$c_key])){ self::$PREPARE[$c_key] = array(); }
		$cached_ids = array_keys(self::$CACHE[$c_key]);

		foreach($owners as $o_id){
			if(!isset($o_id)){ continue; }

			if($options["force_read"]){
				unset(self::$CACHE[$c_key][$o_id]);
			}elseif(in_array($o_id,$cached_ids)){
				continue;
			}

			self::$PREPARE[$c_key][$o_id] = $o_id;
		}
	}

	/**
	 * Flushes out cache for the current owner
	 *
	 * In this example $records and $records2 may vary according to a cache state
	 * ```
	 * $records = $lister->getRecords();
	 * $lister->flushCache();
	 * $records2 = $lister->getRecords();
	 * ```
	 */
	function flushCache(){
		$this->prefetchDataFor($this->_owner,array("force_read" => true));
	}

	/**
	 * Adds a record at the end of the list.
	 *
	 * @param TableRecord $record
	 */
	function append($record){
		$o = $this->_options;
		$this->_add($record);
	}

	/**
	 * Alias for TableRecord_Lister::append().
	 *
	 * @param TableRecord $record
	 */
	function add($record){ return $this->append($record); }

	/**
	 * Prepends a record at the beginning of the list.
	 *
	 * @param TableRecord $record
	 */
	function prepend($record){ $this->_add($record,-1); }

	/**
	 * Alias for TableRecord_Lister::prepend()
	 *
	 * @param TableRecord $record
	 */
	function unshift($record){ return $this->prepend($record); }

	/**
	 * Shift an record off the beginning of the list.
	 *
	 * @return TableRecord $record
	 */
	function shift(){
		$items = $this->getItems();
		if(isset($items[0])){
			$record = $items[0]->getRecord();
			$this->remove($record);
			return $record;
		}
	}

	/**
	 * Internal method to add a record to a collection with set position.
	 *
	 * @param TableRecord $record
	 * @param integer $rank
	 */
	protected function _add($record,$rank = null){
		$o = $this->_options;

		if(is_null($rank)){
			$_rank = $this->_dbmole->selectSingleValue("SELECT MAX($o[rank_field_name])+1 FROM $o[table_name] WHERE $o[owner_field_name]=:owner",array(":owner" => $this->_owner));
			$_rank = isset($_rank) ? $_rank+1 : 0;
		}else{
			$_rank = $rank;
			$this->_correctRanking();
		}

		$o = $this->_options;
		if(!is_null($rank)){
			$this->_dbmole->doQuery("UPDATE $o[table_name] SET $o[rank_field_name]=$o[rank_field_name]+1 WHERE $o[owner_field_name]=:owner AND $o[rank_field_name]>=:rank",array(
				":owner" => $this->_owner,
				":rank" => $rank,
			));
		}
		$this->_dbmole->insertIntoTable($o["table_name"],array(
			$o["id_field_name"] => $this->_dbmole->selectSequenceNextval($o["sequence_name"]),
			$o["owner_field_name"] => $this->_owner,
			$o["subject_field_name"] => $record,
			$o["rank_field_name"] => $_rank,
		));
		$this->_clearCache();
	}

	/**
	 * Removes a record from the list.
	 *
	 * @param TableRecord $record
	 */
	function remove($record){
		$o = $this->_options;
		$this->_dbmole->doQuery("DELETE FROM $o[table_name] WHERE
			$o[owner_field_name]=:owner AND
			$o[subject_field_name]=:record
		",array(":owner" => $this->_owner,":record" => $record));
		$this->_clearCache();
	}

	/**
	 * Removes all items in the list.
	 *
	 * It does not destroy the associated records, only the links from association table.
	 */
	function clear(){
		$o = $this->_options;
		$this->_dbmole->doQuery("DELETE FROM $o[table_name] WHERE
			$o[owner_field_name]=:owner
		",array(":owner" => $this->_owner));
		$this->_clearCache();
	}

	/**
	 * Does the list contain given record?
	 *
	 * @param TableRecord $record
	 * @return bool
	 */
	function contains($record){
		if(is_object($record)){ $record = $record->getId(); }
		foreach($this->getItems() as $item){
			if($item->getRecordId()==$record){ return true; }
		}
		return false;
	}

	/**
	 * Returns number of items in the lister
	 *
	 * @return int
	 */
	function size(){ return sizeof($this->getItems()); }

	/**
	 * Checks if lister contains items.
	 *
	 * @return bool
	 */
	function isEmpty(){ return $this->size()==0; }

	/**
	 * Returns items from association table.
	 *
	 * This method only returns records from association table that hold additional information such as position in collection.
	 * To get associated records use method getRecords().
	 *
	 * @param array $options
	 * - force_read `true` clears cache and reads dall records in a fresh state [default: false]
	 * @return TableRecord_ListerItem[]
	 */
	function &getItems($options = array()){
		$options += array(
			"force_read" => false,
			"preread_data" => true,
		);

		if($options["force_read"]){
			$this->flushCache();
		}

		$o = $this->_options;
		$c_key = $this->_getCacheKey();
		$owner_id = $this->_getOwnerId();

		if(isset(self::$CACHE[$c_key][$owner_id])){
			return self::$CACHE[$c_key][$owner_id];
		}

		if($options["preread_data"]){
			$cacher = Cache::GetObjectCacher($this->_getOwnerClass());
			$this->prefetchDataFor($cacher->cachedIds());
		}

		$ids_to_read = isset(self::$PREPARE[$c_key]) ? self::$PREPARE[$c_key] : array();
		$ids_to_read[$owner_id] = $owner_id;
		unset(self::$PREPARE[$c_key]);

		foreach($ids_to_read as $id){
			self::$CACHE[$c_key][$id] = array();
		}

		$rows = $this->_dbmole->selectRows("
			SELECT
				$o[id_field_name] AS id,
				$o[owner_field_name] AS owner_id,
				$o[subject_field_name] AS record_id,
				$o[rank_field_name] AS rank
			FROM $o[table_name] WHERE
				$o[owner_field_name] IN :ids_to_read ORDER BY $o[rank_field_name], $o[id_field_name]
		",array(
			":ids_to_read" => $ids_to_read
		));
		$rank = 0;
		foreach($rows as $row){
			self::$CACHE[$c_key][$row["owner_id"]][] = new TableRecord_ListerItem($this,$row,$rank);
			//Cache::Prepare($this->getClassNameOfRecords(),$row["record_id"]);
			$rank++;
		}

		return self::$CACHE[$c_key][$owner_id];
	}

	/**
	 * ```
	 *	$ids = $lister->getIds(); // integer[]
	 *	$ids = $lister->getIds(array("force_read" => true)); // If the cache state could be stale
	 * ```
	 *
	 * @return integer[]
	 */
	function getIds($options = array()){
		$out = array();
		foreach($this->getItems($options) as $item){ $out[] = $item->getId(); }
		return $out;
	}

	/**
	 * Returns record ids of associated table.
	 *
	 * ```
	 *	$ids = $lister->getRecordIds(); // integer[]
	 *	$ids = $lister->getRecordIds(array("force_read" => true)); // If the cache state could be stale
	 * ```
	 *
	 * @return integer[]
	 */
	function getRecordIds($options = array()){
		$out = array();
		foreach($this->getItems($options) as $item){ $out[] = $item->getRecordId(); }
		return $out;
	}

	/**
	 * Returns classname of associated records.
	 *
	 * @return string
	 */
	function getClassNameOfRecords(){
		return $this->_options["class_name"];
	}

	/**
	 * Returns records from associated table.
	 *
	 * ```
	 * $lister = $article->getLister("Authors");
	 * $authors = $lister->getRecords(); // array of models
	 * $authors = $lister->getRecords(array("force_read" => true)); // If the cache state could be stale
	 * $authors = $lister->getRecords(array("preread_data" => false)); // When it's not desired to preread data for every cached object lister
	 * ```
	 *
	 * @param array $options
	 * - force_read {@link getItems}
	 * @return TableRecord[]
	 */
	function getRecords($options = array()){
		$ids = array();
		foreach($this->getItems($options) as $item){ $ids[] = $item->getRecordId(); }
		return Cache::Get($this->getClassNameOfRecords(), $ids); // TODO: usage of the Cache should be set by an option
	}

	/**
	 * Set records associated to the object.
	 *
	 * Currently associated records are destroyed and new are appended to the collection.
	 *
	 * Get new lister and associate new records
	 *
	 * ```
	 *	$lister = $article->getLister("Authors");
	 *	$lister->setRecords(array(123,124,125));
	 *	$lister->setRecords(array($obj1,$obj2,$obj3));
	 * ```
	 *
	 * @param TableRecord[] $records
	 */
	function setRecords($records){

		$records = array_map(
			function($v) { return is_object($v)?$v->getId():$v; },
		$records);
		$insert = array();

		$rec = reset($records);
		foreach($this->getItems() as $item){
			if($rec === false){
				$item->destroy();
				continue;
			}
			if($item->getRecordId()!=$rec) {
					$insert[] = array($rec, $item->getRank());
					$item->destroy();
			}
			$rec = next($records);
		}

		foreach($insert as $i) {
			$this->_add($i[0], $i[1]);
		}

		while($rec !== false){
			$this->append($rec);
			$rec = next($records);
		}

		$this->_clearCache();
	}

	/**
	 * Gets the position of a given record in the list.
	 *
	 * ```
	 *	echo $lister->getRecordRank($author); // 0
	 * ```
	 * @param TableRecord $record
	 * @return int
	 */
	function getRecordRank($record){
		$record = TableRecord::ObjToId($record);
		$rank = 0;
		foreach($this->getItems() as $item){
			if($item->getRecordId()==$record){
				return $rank;
			}
			$rank++;
		}
	}

	/**
	 * Sets the position of a given record in the list.
	 *
	 * Move the given author to beginnig of collection
	 * 	$lister->setRecordRank($author,0);
	 *
	 * @param TableRecord $record
	 * @param integer $rank
	 */
	function setRecordRank($record,$rank){
		$record = TableRecord::ObjToId($record);
		foreach($this->getItems() as $item){
			if($item->getRecordId()==$record){
				$item->setRank($rank);
				break;
			}
		}
	}

	/**
	 * Internal method to update ranking of records to contain correct data.
	 *
	 * It is applied after every position update.
	 *
	 * You can specify record which associated records should be reranked.
	 *
	 * @todo make it protected
	 * @param TableRecord|null $owner
	 */
	function _correctRanking($owner = null){
		if(!isset($owner)){ $owner = $this->_owner; }

		$o = $this->_options;
		$rows = $this->_dbmole->selectRows("
			SELECT
				$o[id_field_name] AS id,
				$o[rank_field_name] AS rank
			FROM $o[table_name] WHERE
				$o[owner_field_name]=:owner ORDER BY $o[rank_field_name], $o[id_field_name]
		",array(
			":owner" => $owner
		));
		$expected_rank = 0;
		foreach($rows as $row){
			if($row["rank"]!=$expected_rank){
				$this->_dbmole->doQuery("UPDATE $o[table_name] SET $o[rank_field_name]=:expected_rank WHERE $o[id_field_name]=:id",array(
					":expected_rank" => $expected_rank,
					":id" => $row["id"],
				));
			}
			$expected_rank++;
		}
		$this->_clearCache($owner);
	}

	protected function _getOwnerId(){
		return $this->_owner->getId();
	}

	protected function _getOwnerClass(){
		return get_class($this->_owner);
	}

	function _clearCache($owner = null){
		if(!isset($owner)){ $owner = $this->_owner; }
		$owner_id = TableRecord::ObjToId($owner);

		$c_key = $this->_getCacheKey($owner);
		if(isset(self::$CACHE[$c_key])){
			unset(self::$CACHE[$c_key][$owner_id]);
		}
	}

	protected function _getCacheKey(){
		$options = $this->_options;
		return serialize($options);
	}

	/*** functions implementing array like access ***/
	/**
	 * @ignore
	 */
	function offsetGet($offset) {
		$items = $this->getItems();
		return $items[$offset]->getRecord();
	}

	/**
	 * @ignore
	 */
	function offsetSet($offset, $record)	{
		$items = $this->getItems();
		if (is_null($offset)) {
			$this->append($record);
		} else {
			settype($offset,"integer");
			if (isset($items[$offset])) {
				$items[$offset]->destroy();
			}
			$this->append($record);
			$this->setRecordRank($record,$offset);
		}
	}

	/**
	 * @ignore
	 */
	function offsetUnset($offset) {
		$items = $this->getItems();
		$items[$offset]->destroy();
		$this->_clearCache();
	}

	/**
	 * @ignore
	 */
	function offsetExists($offset) {
		$items = $this->getItems();
		return array_key_exists($offset, $items);
	}

	/*** functions implementing iterator like access (foreach cycle)***/

	/**
	 * @ignore
	 */
	public function current() {
		$items = $this->getItems();
		return $items[$this->iterator_offset]->getRecord();
	}

	/**
	 * @ignore
	 */
	public function key() {
		return $this->iterator_offset;
	}

	/**
	 * @ignore
	 */
	public function next() {
		++$this->iterator_offset;
	}

	/**
	 * @ignore
	 */
	public function rewind() {
		$this->iterator_offset = 0;
	}

	/**
	 * @ignore
	 */
	public function valid()	{
		$items = $this->getItems();
		return isset($items[$this->iterator_offset]);
	}

	/**
	 * @ignore
	 */
	public function count()
	{
		$items = $this->getItems();
		return count($items);
	}

	/**
	 * Cleares whole cache
	 */
	static function ClearCache() {
		self::$CACHE = array();
	}
}

/**
 * Here is a item from a lister.
 */
class TableRecord_ListerItem{

	/**
	 * This constructor is only used internally in {@link TableRecord_Lister TableRecord_Lister} and is not needed to use in an application.
	 *
	 * @param TableRecord_Lister $lister
	 * @param array $row_data
	 * @access private
	 */
	function __construct(&$lister,$row_data,$rank){
		$this->_lister = &$lister;
		$this->_options = $lister->_options;
		$this->_row_data = $row_data;
		$this->_owner = &$lister->_owner;
		$this->_dbmole = &$lister->_dbmole;
		$this->_rank = (int)$rank;
	}

	/**
	 * Gets rank/position of a record in a collection.
	 *
	 * @return integer
	 */
	function getRank(){
		return $this->_rank;
	}

	/**
	 * Get the rank of a record save in the database
	 *
	 * It's useful for testing purposes
	 */
	function _getSavedRank(){
		return (int)$this->_g("rank");
	}

	/**
	 * Returns id of the item.
	 *
	 * @return integer
	 */
	function getId(){
		return (int)$this->_g("id");
	}

	/**
	 * Sets rank/position of the record.
	 *
	 * After setting the rank of the record positions of all items in the collection are corrected.
	 *
	 * @param integer $rank
	 */
	function setRank($rank){
		$rank = (integer)$rank;
		$o = $this->_options;

		if($rank==$this->getRank()){ return; }

		if($rank<0){ $rank = 0; }
		if($rank>=($c = $this->_lister->count())){ $rank = $c-1; }
		$current_rank = $this->getRank();

		$this->_lister->_correctRanking();

		$this->_dbmole->doQuery("UPDATE $o[table_name] SET $o[rank_field_name]=$o[rank_field_name]-1 WHERE $o[rank_field_name]>:current_rank AND $o[owner_field_name]=:owner AND $o[id_field_name]!=:id",array(
			":current_rank" => $current_rank,
			":owner" => $this->_owner,
			":id" => $this,
		));

		$this->_dbmole->doQuery("UPDATE $o[table_name] SET $o[rank_field_name]=:rank WHERE $o[id_field_name]=:id",array(
			":rank" => $rank,
			":id" => $this,
		));

		$this->_dbmole->doQuery("UPDATE $o[table_name] SET $o[rank_field_name]=$o[rank_field_name]+1 WHERE $o[rank_field_name]>=:rank AND $o[owner_field_name]=:owner AND $o[id_field_name]!=:id",array(
			":rank" => $rank,
			":owner" => $this->_owner,
			":id" => $this,
		));

		$this->_lister->_clearCache();

		$this->_s("rank",$rank);
		$this->_rank = $rank;
	}

	/**
	 * Gets id of the associated record.
	 *
	 * @return integer
	 */
	function getRecordId(){
		$id = $this->_g("record_id");
		if(is_numeric($id)){ settype($id,"integer"); }
		return $id;
	}

	/**
	 * Associates another record to the item by setting id of the record to association table.
	 *
	 * @param $record TableRecord
	 */
	function setRecordId($record){
		$o = $this->_options;
		$this->_dbmole->doQuery("UPDATE $o[table_name] SET $o[subject_field_name]=:record WHERE $o[id_field_name]=:id",array(
			":record" => $record,
			":id" => $this,
		));
	}

	/**
	 * Gets record associated to the TableRecord_ListerItem.
	 *
	 * @return TableRecord
	 */
	function getRecord(){
		return Cache::Get($this->_options["class_name"],$this->getRecordId());
	}

	/**
	 * Destroys item with associated record.
	 */
	function destroy(){
		$o = $this->_options;
		$this->_dbmole->doQuery("DELETE FROM $o[table_name] WHERE $o[id_field_name]=:id",array(
			":id" => $this,
		));
	}

	/**
	 * Internal method to get value from association table.
	 *
	 * @param string $key
	 */
	function _g($key){
		return $this->_row_data[$key];
	}

	/**
	 * Internal method to set value from association table.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	function _s($key,$value){
		$this->_row_data[$key] = $value;
	}
}

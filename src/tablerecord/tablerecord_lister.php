<?php
/**
 * Class for managing sortable records.
 *
 * @package Atk14\TableRecord
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
 * 	$article = Article::GetInstanceById(1);
 * 	$lister = $article->getLister("Authors");
 * 	$lister->append($author1);
 * 	$lister->append($author2);
 * 	$lister->getRecords(); // array($author1,$author2);
 * 	$lister->contains($author1); // true
 * 	$lister->contains($author3); // false
 * 	$items = $lister->getItems();
 * 	$items[0]->getRecord(); // $author1
 * 	$items[1]->getRecord(); // $author2
 *
 * 	$items[0]->getRank(); // 0
 * 	$items[1]->setRank(0); //
 * 	$items[0]->getRank(); // 1
 *
 * 	$lister->setRecordRank($author2,0);
 *
 * @package Atk14\TableRecord
 * @param TableRecord $owner
 * @param String $subjects
 * @param array $options
 */
class TableRecord_Lister extends inobj implements ArrayAccess, Iterator, Countable {
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
	 * 	$authors_lister = new TableRecord_Lister($article,"Authors",array(
	 * 	));
	 *
	 * Description $options:
	 * - class_name
	 * - table_name - name of table used as association table
	 * - id_field_name - name of the field used as primary key of the association table
	 * - owner_field_name - name of the foreign key to connect associating table and owning table
	 * - subject_field_name - name of the foreign key used to connect associating table and subjects table
	 * - rank_field_name - name of the field used for sorting
	 *
	 * @param $owner TableRecord TableRecord instance to which the $subjects are associated
	 * @param string $subjects name of associated table is derived from subjects
	 * @param array $options
	 *
	 * @todo comment options
	 */
	function TableRecord_Lister($owner,$subjects,$options = array()){
		$owner_class = new String(get_class($owner));
		$owner_class_us = $owner_class->underscore();
		$subjects = new String($subjects);
		$subjects_us = $subjects->underscore();
		$subject = $subjects->singularize();
		$subject_us = $subject->underscore();

		$options = array_merge(array(
			"class_name" => $subject, // Author
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
		$this->_dbmole = &$owner->_dbmole;
		$this->_options = $options;
	}

	/**
	 * Adds a record at the end of the list.
	 *
	 * @param TableRecord $record
	 */
	function append($record){
		$o = $this->_options;
		$rank = $this->_dbmole->selectSingleValue("SELECT MAX($o[rank_field_name]) FROM $o[table_name] WHERE $o[owner_field_name]=:owner",array(":owner" => $this->_owner));
		$rank = isset($rank) ? $rank+1 : 0;

		$this->_add($record,$rank);
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
	 * @returns TableRecord $record
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
	protected function _add($record,$rank){
		$o = $this->_options;
		$this->_dbmole->insertIntoTable($o["table_name"],array(
			$o["id_field_name"] => $this->_dbmole->selectSequenceNextval($o["sequence_name"]),
			$o["owner_field_name"] => $this->_owner,
			$o["subject_field_name"] => $record,
			$o["rank_field_name"] => $rank,
		));

		$this->_correctRanking();
		unset($this->_items);
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
		$this->_correctRanking();
		unset($this->_items);
	}

	/**
	 * Removes all items in the list.
	 */
	function clear(){
		$o = $this->_options;
		$this->_dbmole->doQuery("DELETE FROM $o[table_name] WHERE
			$o[owner_field_name]=:owner
		",array(":owner" => $this->_owner));
		unset($this->_items);
	}

	/**
	 * Does the list contain given record?
	 *
	 * @param TableRecord $record
	 * @returns bool
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
	 * @returns int
	 */
	function size(){ return sizeof($this->getItems()); }

	/**
	 * Checks if lister contains items.
	 *
	 * @returns bool
	 */
	function isEmpty(){ return $this->size()==0; }

	/**
	 * Returns items from association table.
	 *
	 * This method only returns records from association table that hold additional information such as position in collection.
	 * To get associated records use method getRecords().
	 *
	 * @returns TableRecord_ListerItem[]
	 */
	function getItems(){
		$this->_readItems();
		return $this->_items;
	}

	/**
	 * Returns record ids of associated table.
	 *
	 * @returns integer[]
	 */
	function getRecordIds(){
		$out = array();
		foreach($this->getItems() as $item){ $out[] = $item->getRecordId(); }
		return $out;
	}

	/**
	 *
	 */
	function getClassNameOfRecords(){
		return $this->_options["class_name"];
	}

	/**
	 * Returns records from associated table.
	 *
	 *	$lister = $article->getLister("Authors");
	 *	$authors = $lister->getRecords(); // array of models
	 *
	 * @returns TableRecord[]
	 */
	function getRecords(){
		$out = array();
		foreach($this->getItems() as $item){ $out[] = $item->getRecord(); }
		return $out;
	}

	/**
	 * Set records associated to the object.
	 *
	 * Currently associated records are destroyed and new are appended to the collection.
	 *
	 * Get new lister and associate new records
	 *
	 * 	$lister = $article->getLister("Authors");
	 * 	$lister->setRecords(array(123,124,125));
	 * 	$lister->setRecords(array($obj1,$obj2,$obj3));
	 *
	 * @param TableRecord[] $records
	 */
	function setRecords($records){
		reset($records);
		foreach($this->getItems() as $item){
			if(!$rec = array_shift($records)){
				$item->destroy(array("__auto_correct_ranking__" => false));
				continue;
			}
			$rec = is_object($rec) ? $rec->getId() : $rec;
			if($item->getRecordId()!=$rec){ $item->setRecordId($rec); }
		}
		while($rec = array_shift($records)){
			$this->append($rec);
		}

		$this->_correctRanking();
	}

	/**
	 * Sets position of a record in the list.
	 *
	 * Move the given author to beginnig of collection
	 * 	$lister->setRecordRank($author,0);
	 *
	 * @param TableRecord $record
	 * @param integer $rank
	 */
	function setRecordRank($record,$rank){
		$record = $this->_objToId($record);
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
	 * @todo make it protected
	 */
	function _correctRanking(){
		$o = $this->_options;
		$rows = $this->_dbmole->selectRows("
			SELECT
				$o[id_field_name] AS id,
				$o[rank_field_name] AS rank
			FROM $o[table_name] WHERE
				$o[owner_field_name]=:owner ORDER BY $o[rank_field_name], $o[id_field_name]
		",array(
			":owner" => $this->_owner
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
		unset($this->_items);
	}

	/**
	 * Internal method to read items from table to internal memory.
	 *
	 * @returns TableRecord_ListerItem[]
	 */
	private function _readItems(){
		$o = $this->_options;
		if(isset($this->_items)){ return; }
		$rows = $this->_dbmole->selectRows("
			SELECT
				$o[id_field_name] AS id,
				$o[subject_field_name] AS record_id,
				$o[rank_field_name] AS rank
			FROM $o[table_name] WHERE
				$o[owner_field_name]=:owner ORDER BY $o[rank_field_name], $o[id_field_name]
		",array(
			":owner" => $this->_owner
		));
		$this->_items = array();
		foreach($rows as $row){
			$this->_items[] = new TableRecord_ListerItem($this,$row);
		}
	}

	/*** functions implementing array like access ***/
	/**
	 * @ignore
	 */
	function offsetGet($value) {
		$x=$this->getItems();
		return $x[$value]->getRecord();
	}

	/**
	 * @ignore
	 */
	function offsetSet($offset, $record)	{
		$this->getItems();
		if (is_null($offset)) {
			$this->append($record);
		} else {
			settype($offset,"integer");
			if (isset($this->_items[$offset])) {
				$this->_items[$offset]->destroy();
			}
			$this->append($record);
			$this->setRecordRank($record,$offset);
		}
	}

	/**
	 * @ignore
	 */
	function offsetUnset($value) {
		$this->getItems();
		$this->_items[$value]->destroy();
	}

	/**
	 * @ignore
	 */
	function offsetExists($value) {
		$this->getItems();
		return array_key_exists($name, $this->_items);
	}

	/*** functions implementing iterator like access (foreach cycle)***/
	/**
	 * @ignore
	 */
	public function current() {
		return current($this->_items)->getRecord();
	}

	/**
	 * @ignore
	 */
	public function key() {
		return key($this->_items);
	}

	/**
	 * @ignore
	 */
	public function next() {
		return next($this->_items);
	}

	/**
	 * @ignore
	 */
	public function rewind() {
		$this->getItems();
		return reset($this->_items);
	}

	/**
	 * @ignore
	 */
	public function valid()	{
		return isset($this->_items) && current($this->_items);
	}

	/**
	 * @ignore
	 */
	public function count()
	{
		$this->getItems();
		return count($this->_items);
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
	function TableRecord_ListerItem(&$lister,$row_data){
		$this->_lister = &$lister;
		$this->_options = $lister->_options;
		$this->_row_data = $row_data;
		$this->_owner = &$lister->_owner;
		$this->_dbmole = &$lister->_dbmole;
	}

	/**
	 * Gets rank/position of a record in a collection.
	 *
	 * @return integer
	 */
	function getRank(){
		return (int)$this->_g("rank");
	}

	/**
	 * Returns id of the item.
	 *
	 * @returns integer
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
		$o = $this->_options;
		settype($rank,"integer");
		if($rank==$this->getRank()){ return; }
		if($rank>$this->getRank()){
			$this->_dbmole->doQuery("UPDATE $o[table_name] SET $o[rank_field_name]=$o[rank_field_name]-1 WHERE $o[rank_field_name]<=:rank AND $o[owner_field_name]=:owner AND $o[id_field_name]!=:id",array(
				":rank" => $rank,
				":owner" => $this->_owner,
				":id" => $this,
			));
		}else{
			$this->_dbmole->doQuery("UPDATE $o[table_name] SET $o[rank_field_name]=$o[rank_field_name]+1 WHERE $o[rank_field_name]>=:rank AND $o[owner_field_name]=:owner AND $o[id_field_name]!=:id",array(
				":rank" => $rank,
				":owner" => $this->_owner,
				":id" => $this,
			));
		}
		$this->_dbmole->doQuery("UPDATE $o[table_name] SET $o[rank_field_name]=:rank WHERE $o[id_field_name]=:id",array(
			":rank" => $rank,
			":id" => $this,
		));
		$this->_lister->_correctRanking();
		$this->_s("rank",$rank);
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
	 *
	 * @param array $options only __auto_correct_ranking__  can be set to (not) correct ranking of other items. Defaults to true
	 */
	function destroy($options = array()){
		$options = array_merge(array(
			"__auto_correct_ranking__" => true,
		),$options);
		$o = $this->_options;
		$this->_dbmole->doQuery("DELETE FROM $o[table_name] WHERE $o[id_field_name]=:id",array(
			":id" => $this,
		));

		if($options["__auto_correct_ranking__"]){
			$this->_lister->_correctRanking();
		}
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

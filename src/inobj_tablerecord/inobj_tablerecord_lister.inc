<?php
/** 
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
 */
class inobj_TableRecord_Lister extends inobj{
	/**
	 * $authors_lister = new inobj_TableRecord_Lister($article,"Authors",array(
	 *	
	 * ));
	 *
	 */
	function inobj_TableRecord_Lister($owner,$subjects,$options = array()){
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
	 * Adds an record at the end of the list.
	 */
	function append($record){
		$o = $this->_options;
		$rank = $this->_dbmole->selectSingleValue("SELECT MAX($o[rank_field_name]) FROM $o[table_name] WHERE $o[owner_field_name]=:owner",array(":owner" => $this->_owner));
		$rank = isset($rank) ? $rank+1 : 0;

		$this->_add($record,$rank);
	}
	
	/**
	 * Alias for inobj_TableRecord_Lister::append().
	 */
	function add($record){ return $this->append($record); }

	/**
	 * Pepends a record at the beginning of the list.
	 */
	function prepend($record){ $this->_add($record,-1); }

	/**
	 * Alias for inobj_TableRecord_Lister::prepend()
	 */	
	function unshift($record){ return $this->prepend($record); }

	/**
	 * Shift an record off the beginning of the list.
	 */
	function shift(){
		$items = $this->getItems();
		if(isset($items[0])){
			$record = $items[0]->getRecord();
			$this->remove($record);
			return $record;
		}
	}

	function _add($record,$rank){
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
	 */
	function contains($record){
		if(is_object($record)){ $record = $record->getId(); }
		foreach($this->getItems() as $item){
			if($item->getRecordId()==$record){ return true; }
		}
		return false;
	}

	function size(){ return sizeof($this->getItems()); }

	function isEmpty(){ return $this->size()==0; }

	function getItems(){
		$this->_readItems();
		return $this->_items;
	}

	function getRecords(){
		$out = array();
		foreach($this->getItems() as $item){ $out[] = $item->getRecord(); }
		return $out;
	}

	/**
	 * $lister->setRecordRank($author,0); // moves the given author to begin
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

	function _readItems(){
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
			$this->_items[] = new inobj_TableRecord_ListerItem($this,$row);
		}
	}
}

class inobj_TableRecord_ListerItem{

	/**
	 * @access private
	 */
	function inobj_TableRecord_ListerItem(&$lister,$row_data){
		$this->_lister = &$lister;
		$this->_options = $lister->_options;
		$this->_row_data = $row_data;
		$this->_owner = &$lister->_owner;
		$this->_dbmole = &$lister->_dbmole;
	}

	function getRank(){
		return (int)$this->_g("rank");
	}

	function getId(){
		return (int)$this->_g("id");
	}

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

	function getRecordId(){
		$id = $this->_g("record_id");
		if(is_numeric($id)){ settype($id,"integer"); }
		return $id;
	}

	function getRecord(){
		return Cache::Get($this->_options["class_name"],$this->getRecordId());
	}

	function _g($key){
		return $this->_row_data[$key];
	}

	function _s($key,$value){
		$this->_row_data[$key] = $value;
	}
}

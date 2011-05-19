<?
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
 * $items[0]->getRank(); // 0
 * $items[1]->setRank(0); //
 * $items[0]->getRank(); // 1
 */
class inobj_TableRecord_Lister{
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

	}

	function append($record){
		
	}

	function prepend($record){

	}

	function remove($record){

	}

	function contains($record){

	}

	function getItems(){
		return $this->_Items;
	}

	function getRecords(){
		$out = array();
		foreach($this->getItems() as $item){ $out[] = $item->getRecord(); }
		return $out;
	}

	function _sort(){

	}

	function _readItems(){
		// nene!! toto je spis getRecords()
		$this->_Items = $class_name::GetInstanceById($this->_dbmole->selectIntoArray("SELECT $subject_field_name FROM $table_name WHERE $owner_field_name=:owner ORDER BY rank"));
	}
}

class inobj_TableRecord_ListerItem{
	function getRank(){

	}
	function setRank(){

	}
	function getRecord(){

	}
}

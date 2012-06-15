<?php
/**
* Testovaci trida urcena pro testovani TableRecord()
*/
class TestTable extends TableRecord{

	function TestTable(){
		TableRecord::TableRecord("test_table",array("sequence_name" => "test_table_id_seq"));
	}

	/*
	static function GetInstanceById($id){
		return TableRecord::_GetInstanceById("TestTable",$id);
	}

	static function CreateNewRecord($values,$options = array()){
		return TestTable::_CreateNewRecord("TestTable",$values,$options);
	}

	function getTitle(){ return $this->g("title"); }
	*/
}

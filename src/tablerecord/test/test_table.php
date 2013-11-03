<?php
/**
 * A class for testing purposes
 */
class TestTable extends TableRecord{

	function __construct(){
		//parent::__construct("test_table",array("sequence_name" => "test_table_id_seq"));
		// or
		parent::__construct(array(
			"table_name" => "test_table",
			"sequence_name" => "test_table_id_seq",
		));
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

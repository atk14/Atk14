<?
/**
* Testovaci trida urcena pro testovani inobj_TableRecord()
* SQL struktura zde: https://svn.ntvage.cz:446/sources/trunk/dbmole/test/testing_structures.sql
*/
class inobj_TestTable extends inobj_TableRecord{

	function inobj_TestTable(){
		inobj_TableRecord::inobj_TableRecord("test_table",array("sequence_name" => "test_table_id_seq"));
	}

	/*
	static function GetInstanceById($id){
		return inobj_TableRecord::_GetInstanceById("inobj_TestTable",$id);
	}

	static function CreateNewRecord($values,$options = array()){
		return inobj_TestTable::_CreateNewRecord("inobj_TestTable",$values,$options);
	}

	function getTitle(){ return $this->g("title"); }
	*/
}

<?php
class TcCache extends TcBase{
	function test(){
		$record = TestTable::CreateNewRecord(array());

		$rec = Cache::Get("TestTable",$record->getId());
		$this->assertEquals($record->getId(),$rec->getId());

		$rec = Cache::Get("TestTable",$record);
		$this->assertEquals($record->getId(),$rec->getId());

		$this->assertEquals(null,Cache::Get("TestTable",null));

		// reading objects by an array

		$recs = Cache::Get("TestTable",array($record->getId()));
		$this->assertEquals($record->getId(),$recs[0]->getId());

		$recs = Cache::Get("TestTable",array($record));
		$this->assertEquals($record->getId(),$recs[0]->getId());

		$this->assertEquals(array(null),Cache::Get("TestTable",array(null)));

		$recs = Cache::Get("TestTable",array("a" => $record->getId(), "b" => $record, "c" => null));
		$this->assertEquals($record->getId(),$recs["a"]->getId());
		$this->assertEquals($record->getId(),$recs["b"]->getId());
		$this->assertEquals(null,$recs["c"]);
	}
}

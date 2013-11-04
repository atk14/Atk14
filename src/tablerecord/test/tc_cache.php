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

		//

		$record = TestTable::CreateNewRecord(array(
			"title" => "The_Elephant_Song"
		));
		$record2 = TestTable::GetInstanceById($record);

		$cached_r1 = Cache::Get("TestTable",$record);
		$cached_r2 = Cache::Get("TestTable",$record);
		$this->assertEquals("The_Elephant_Song",$cached_r1->getTitle());
		$this->assertEquals("The_Elephant_Song",$cached_r2->getTitle());

		// magic! objects returned from the cache are the same
		$cached_r1->s("title","The_Squirrel_Dance");
		$this->assertEquals("The_Squirrel_Dance",$cached_r1->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r2->getTitle());

		//
		$record->s("title","The_Crocodile_Singing");

		$this->assertEquals("The_Crocodile_Singing",$record->getTitle());
		$this->assertEquals("The_Elephant_Song",$record2->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r1->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r2->getTitle());

		//
		Cache::Clear();
		$cached_r3 = Cache::Get("TestTable",$record);

		$this->assertEquals("The_Crocodile_Singing",$record->getTitle());
		$this->assertEquals("The_Elephant_Song",$record2->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r1->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r2->getTitle());
		$this->assertEquals("The_Crocodile_Singing",$cached_r3->getTitle());
	}
}

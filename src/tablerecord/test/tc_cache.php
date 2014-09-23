<?php
class TcCache extends TcBase{
	function test(){
		$record1 = TestTable::CreateNewRecord(array());

		$this->assertEquals(array(),Cache::CachedIds("TestTable"));

		$rec = Cache::Get("TestTable",$record1->getId());
		$this->assertEquals($record1->getId(),$rec->getId());

		$this->assertEquals(array($rec->getId()),Cache::CachedIds("TestTable"));

		$rec = Cache::Get("TestTable",$record1);
		$this->assertEquals($record1->getId(),$rec->getId());

		$this->assertEquals(null,Cache::Get("TestTable",null));

		// reading objects by an array

		$recs = Cache::Get("TestTable",array($record1->getId()));
		$this->assertEquals($record1->getId(),$recs[0]->getId());

		$recs = Cache::Get("TestTable",array($record1));
		$this->assertEquals($record1->getId(),$recs[0]->getId());

		$this->assertEquals(array(null),Cache::Get("TestTable",array(null)));

		$recs = Cache::Get("TestTable",array("a" => $record1->getId(), "b" => $record1, "c" => null));
		$this->assertEquals($record1->getId(),$recs["a"]->getId());
		$this->assertEquals($record1->getId(),$recs["b"]->getId());
		$this->assertEquals(null,$recs["c"]);

		//

		$record2 = TestTable::CreateNewRecord(array(
			"title" => "The_Elephant_Song"
		));
		$record3 = TestTable::GetInstanceById($record2);

		$cached_r1 = Cache::Get("TestTable",$record2);
		$cached_r2 = Cache::Get("TestTable",$record2);
		$this->assertEquals("The_Elephant_Song",$cached_r1->getTitle());
		$this->assertEquals("The_Elephant_Song",$cached_r2->getTitle());

		// magic! objects returned from the cache are the same
		$cached_r1->s("title","The_Squirrel_Dance");
		$this->assertEquals("The_Squirrel_Dance",$cached_r1->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r2->getTitle());

		//
		$record2->s("title","The_Crocodile_Singing");

		$this->assertEquals("The_Crocodile_Singing",$record2->getTitle());
		$this->assertEquals("The_Elephant_Song",$record3->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r1->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r2->getTitle());

		$this->assertEquals(array($record1->getId(),$record2->getId()),Cache::CachedIds("TestTable"));

		//
		Cache::Clear();
		$this->assertEquals(array(),Cache::CachedIds("TestTable"));
		$cached_r3 = Cache::Get("TestTable",$record2);
		$this->assertEquals(array($record2->getId()),Cache::CachedIds("TestTable"));

		$this->assertEquals("The_Crocodile_Singing",$record2->getTitle());
		$this->assertEquals("The_Elephant_Song",$record3->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r1->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r2->getTitle());
		$this->assertEquals("The_Crocodile_Singing",$cached_r3->getTitle());

	}
}

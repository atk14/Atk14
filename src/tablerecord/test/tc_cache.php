<?php
class TcCache extends TcBase{
	function test(){
		$record1 = TestTable::CreateNewRecord(array());

		$this->assertEquals(array(),Cache::CachedIds("TestTable"));

		$rec = Cache::Get("TestTable",$record1->getId());
		$this->assertEquals($record1->getId(),$rec->getId());

		$this->assertEquals(array($rec->getId() => $rec->getId()),Cache::CachedIds("TestTable"));

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

		$this->assertEquals(array($record1->getId() => $record1->getId(),$record2->getId() => $record2->getId()),Cache::CachedIds("TestTable"));

		//
		Cache::Clear();
		$this->assertEquals(array(),Cache::CachedIds("TestTable"));
		$cached_r2b = Cache::Get("TestTable",$record2);
		$this->assertEquals(array($record2->getId() => $record2->getId()),Cache::CachedIds("TestTable"));

		$this->assertEquals(array($record2, null),Cache::GetObjectCacher("TestTable")->getCached( array($record2->getId(), $record1->getId() ) ));
		$cached_r1b = Cache::Get("TestTable",$record1);
		$this->assertEquals(array($cached_r2b, $cached_r1b),Cache::GetObjectCacher("TestTable")->getCached( array($record2->getId(), $record1->getId() ) ));
		Cache::Clear("TestTable",$record2);
		$this->assertEquals(array(null, $cached_r1b),Cache::GetObjectCacher("TestTable")->getCached( array($record2->getId(), $record1->getId() ) ));
		Cache::Clear("TestTable");
		$this->assertFalse(Cache::GetObjectCacher('TestTable')->inCache($record2));

		$this->assertEquals(array(),Cache::CachedIds("TestTable"));
		Cache::Prepare('TestTable', $record1);
		$this->assertEquals(array($record1->getId() => $record1->getId()),Cache::CachedIds("TestTable"));
		$this->assertEquals(array(null, null),Cache::GetObjectCacher("TestTable")->getCached( array($record2->getId(), $record1->getId() ) ));

		$this->assertFalse(is_array(Cache::Get('TestTable', $record2)));
		$this->assertEquals(array($record1->getId() => $record1->getId(), $record2->getId() => $record2->getId()),Cache::CachedIds("TestTable"));

		$this->assertFalse(is_array(Cache::Get('TestTable', $record2)));
		$this->assertTrue(is_array(Cache::Get('TestTable', array($record2))));
		$this->assertEquals(7,key(Cache::Get('TestTable', array(7 => $record2))));

		$this->assertTrue(Cache::GetObjectCacher('TestTable')->inCache($record2));


		foreach(array($record1, $record2) as $rec) {
			$out = Cache::GetObjectCacher("TestTable")->getCached(array($rec->getId()));
			$this->assertNotNull($out[0]);
		}

		$this->assertEquals("The_Crocodile_Singing",$record2->getTitle());
		$this->assertEquals("The_Crocodile_Singing",$cached_r2b->getTitle());
		$this->assertEquals("The_Elephant_Song",$record3->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r1->getTitle());
		$this->assertEquals("The_Squirrel_Dance",$cached_r2->getTitle());
	}

	function test_caching_non_existing_record(){
		$dbmole = TestTable::GetDbmole();

		$queries_executed = $dbmole->getQueriesExecuted();
		Cache::Prepare("TestTable",11233);
		$this->assertEquals($queries_executed,$dbmole->getQueriesExecuted());

		$this->assertEquals(null,Cache::Get("TestTable",11233));
		$this->assertEquals($queries_executed+1,$dbmole->getQueriesExecuted());

		$this->assertEquals(null,Cache::Get("TestTable",11233));
		$this->assertEquals($queries_executed+1,$dbmole->getQueriesExecuted());

		Cache::Clear("TestTable");

		$this->assertEquals(null,Cache::Get("TestTable",11233));
		$this->assertEquals($queries_executed+2,$dbmole->getQueriesExecuted());
	}
}

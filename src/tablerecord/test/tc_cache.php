<?php
class TcCache extends TcBase{

	function test(){
		$record1 = TestTable::CreateNewRecord([]);

		$this->assertEquals([],Cache::CachedIds("TestTable"));

		$rec = Cache::Get("TestTable",$record1->getId());
		$this->assertEquals($record1->getId(),$rec->getId());

		$this->assertEquals([$rec->getId() => $rec->getId()],Cache::CachedIds("TestTable"));

		$rec = Cache::Get("TestTable",$record1);
		$this->assertEquals($record1->getId(),$rec->getId());

		$this->assertEquals(null,Cache::Get("TestTable",null));

		// reading objects by an array

		$recs = Cache::Get("TestTable",[$record1->getId()]);
		$this->assertEquals($record1->getId(),$recs[0]->getId());

		$recs = Cache::Get("TestTable",[$record1]);
		$this->assertEquals($record1->getId(),$recs[0]->getId());

		$this->assertEquals([null],Cache::Get("TestTable",[null]));

		$recs = Cache::Get("TestTable",["a" => $record1->getId(), "b" => $record1, "c" => null]);
		$this->assertEquals($record1->getId(),$recs["a"]->getId());
		$this->assertEquals($record1->getId(),$recs["b"]->getId());
		$this->assertEquals(null,$recs["c"]);

		//

		$record2 = TestTable::CreateNewRecord([
			"title" => "The_Elephant_Song"
		]);
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

		$this->assertEquals([$record1->getId() => $record1->getId(),$record2->getId() => $record2->getId()],Cache::CachedIds("TestTable"));

		//
		Cache::Clear();
		$this->assertEquals([],Cache::CachedIds("TestTable"));
		$cached_r2b = Cache::Get("TestTable",$record2);
		$this->assertEquals([$record2->getId() => $record2->getId()],Cache::CachedIds("TestTable"));

		$this->assertEquals([$record2, null],Cache::GetObjectCacher("TestTable")->getCached( [$record2->getId(), $record1->getId() ] ));
		$cached_r1b = Cache::Get("TestTable",$record1);
		$this->assertEquals([$cached_r2b, $cached_r1b],Cache::GetObjectCacher("TestTable")->getCached( [$record2->getId(), $record1->getId() ] ));
		Cache::Clear("TestTable",$record2);
		$this->assertEquals([null, $cached_r1b],Cache::GetObjectCacher("TestTable")->getCached( [$record2->getId(), $record1->getId() ] ));
		Cache::Clear("TestTable");
		$this->assertFalse(Cache::GetObjectCacher('TestTable')->inCache($record2));

		$this->assertEquals([],Cache::CachedIds("TestTable"));
		Cache::Prepare('TestTable', $record1);
		$this->assertEquals([$record1->getId() => $record1->getId()],Cache::CachedIds("TestTable"));
		$this->assertEquals([null, null],Cache::GetObjectCacher("TestTable")->getCached( [$record2->getId(), $record1->getId() ] ));

		$this->assertFalse(is_array(Cache::Get('TestTable', $record2)));
		$this->assertEquals([$record1->getId() => $record1->getId(), $record2->getId() => $record2->getId()],Cache::CachedIds("TestTable"));

		$this->assertFalse(is_array(Cache::Get('TestTable', $record2)));
		$this->assertTrue(is_array(Cache::Get('TestTable', [$record2])));
		$this->assertEquals(7,key(Cache::Get('TestTable', [7 => $record2])));

		$this->assertTrue(Cache::GetObjectCacher('TestTable')->inCache($record2));


		foreach([$record1, $record2] as $rec) {
			$out = Cache::GetObjectCacher("TestTable")->getCached([$rec->getId()]);
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

	function test_caching_null() {
		# using empty string as caching key is ok
		Cache::Get("TestTable", [1123,"",55,0, -1]);
		$cached_ids = Cache::CachedIds("TestTable");
		$this->assertCount(5, $cached_ids);
		$this->assertArrayHasKey('', $cached_ids);
		$this->assertArrayHasKey(1123, $cached_ids);
		$this->assertArrayHasKey(55, $cached_ids);
		$this->assertArrayHasKey(0, $cached_ids);
		$this->assertArrayHasKey(-1, $cached_ids);

		# but null shouldn't be used as id to be cached
		Cache::Clear();
		Cache::Get("TestTable", [1123,null,55,0, -1]);
		$cached_ids = Cache::CachedIds("TestTable");
		$this->assertCount(4, $cached_ids);
		$this->assertArrayNotHasKey('', $cached_ids);
	}

	function test_getCached(){
		$a123 = Article::CreateNewRecord([
			"id" => 123,
			"title" => "Article no 123",
		]);
		$a124 = Article::CreateNewRecord([
			"id" => 124,
			"title" => "Article no 124",
		]);
		$a125 = Article::CreateNewRecord([
			"id" => 125,
			"title" => "Article no 125",
		]);

		Cache::Clear();

		$cacher = Cache::GetObjectCacher("Article");

		$this->assertEquals([],$cacher->getCached());
		$this->assertEquals([],$cacher->getCached(true));

		$this->assertEquals([null,null,null],$cacher->getCached([123,124,125]));
		$this->assertEquals([null,null,null],$cacher->getCached([123,124,125],true));

		Cache::Prepare("Article",123);

		$this->assertEquals([],$cacher->getCached());
		$this->assertEquals([123 => $a123],$cacher->getCached(true));

		$this->assertEquals([$a123,null,null],$cacher->getCached([123,124,125]));

		Cache::Prepare("Article",124);

		$this->assertEquals([$a123,null,null],$cacher->getCached([123,124,125]));
		$this->assertEquals([$a123,$a124,null],$cacher->getCached([123,124,125],true));

		Cache::Get("Article",125);

		$this->assertEquals([$a123,$a124,$a125],$cacher->getCached([123,124,125]));
		$this->assertEquals([123 => $a123,124 => $a124,125 => $a125],$cacher->getCached());
	}
}

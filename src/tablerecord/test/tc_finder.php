<?php
class TcFinder extends TcBase{

	function test(){
		$apples = TestTable::CreateNewRecord([
			"title" => "Apples",
			"an_integer" => 10,
		]);

		$and = TestTable::CreateNewRecord([
			"title" => "and",
			"an_integer" => 10,
		]);

		$oranges = TestTable::CreateNewRecord([
			"title" => "Oranges",
			"an_integer" => 30,
		]);

		$finder = TestTable::Finder([
			"order_by" => "an_integer",
			"limit" => 1,
		]);

		$this->assertEquals(1,$finder->getLimit());
		$this->assertEquals(1,$finder->getPageSize());

		$records = $finder->getRecords();
		$this->assertEquals(3,$finder->getTotalAmount());
		$this->assertEquals(false,$finder->hardLimitReached());
		$this->assertEquals(1,$finder->getRecordsDisplayed());
		$this->assertEquals(1,count($records));
		$this->assertEquals(1,count($finder));
		$this->assertEquals("Apples",$finder[0]->getTitle());
		$this->assertEquals("Apples",$records[0]->getTitle());

		// --
		$finder = TestTable::Finder([
			"order_by" => "an_integer",
			"limit" => 2,
			"offset" => 1,
		]);

		$this->assertEquals(2,$finder->getLimit());
		$this->assertEquals(2,$finder->getPageSize());

		$records = $finder->getRecords();
		$this->assertEquals(3,$finder->getTotalAmount());
		$this->assertEquals(2,$finder->getRecordsDisplayed());
		$this->assertEquals(2,count($records));
		$this->assertEquals(2,count($finder));

		$this->assertEquals("and",$records[0]->getTitle());
		$this->assertEquals("and",$finder[0]->getTitle());

		$this->assertEquals("Oranges",$records[1]->getTitle());
		$this->assertEquals("Oranges",$finder[1]->getTitle());

		// -- locating the position
		$this->assertEquals(false,$finder->atBeginning());
		$this->assertEquals(true,$finder->atEnd());

		$this->assertEquals(null,$finder->getPrevOffset());
		$this->assertEquals(null,$finder->getNextOffset());

		$finder = TestTable::Finder(["limit" => 2, "offset" => 0]);
		$this->assertEquals(true,$finder->atBeginning());
		$this->assertEquals(false,$finder->atEnd());
		$this->assertEquals(null,$finder->getPrevOffset());
		$this->assertEquals(2,$finder->getNextOffset());
		$this->assertEquals(2,$finder->getRecordsDisplayed());

		$finder = TestTable::Finder(["limit" => 2, "offset" => 2]);
		$this->assertEquals(false,$finder->atBeginning());
		$this->assertEquals(true,$finder->atEnd());
		$this->assertEquals(null,$finder->getPrevOffset());
		$this->assertEquals(null,$finder->getNextOffset());
		$this->assertEquals(1,$finder->getRecordsDisplayed());


		$finder = TestTable::Finder(["limit" => 1, "offset" => 1]);
		$this->assertEquals(false,$finder->atBeginning());
		$this->assertEquals(false,$finder->atEnd());
		$this->assertEquals(null,$finder->getPrevOffset());
		$this->assertEquals(2,$finder->getNextOffset());
		$this->assertEquals(1,$finder->getRecordsDisplayed());

		// --
		$finder = TestTable::Finder("an_integer",10,["order_by" => "UPPER(title) DESC"]);

		$this->assertEquals(20,$finder->getLimit()); // default
		$this->assertEquals(20,$finder->getPageSize());

		$records = $finder->getRecords();
		$record_ids = $finder->getRecordIds();
		$this->assertEquals(2,count($record_ids));
		$this->assertEquals(2,count($records));
		$this->assertEquals($apples->getId(),$records[0]->getId());
		$this->assertEquals($and->getId(),$records[1]->getId());
		$this->assertEquals((int)$apples->getId(),(int)$record_ids[0]);
		$this->assertEquals((int)$and->getId(),(int)$record_ids[1]);

		// --
		$finder = TestTable::Finder("an_integer",10,"title","Apples",["order_by" => "UPPER(title) DESC"]);

		$records = $finder->getRecords();
		$this->assertEquals(1,count($records));
		$this->assertEquals($apples->getId(),$records[0]->getId());

		// -- searching by a custom query
		$finder = TestTable::Finder([
			"query" => "SELECT id FROM test_table ORDER BY UPPER(title) ASC",
			"query_count" => "SELECT COUNT(*) FROM test_table",
			"order_by" => null, // disabling the default ordering
			"limit" => 2,
			"offset" => 1,
		]);

		$this->assertEquals(3,$finder->getTotalAmount());
		$this->assertEquals(2,count($finder));
		$this->assertEquals("Apples",$finder[0]->getTitle());
		$this->assertEquals("Oranges",$finder[1]->getTitle());
	}

	function test_iterate_records(){
		// an_integer is deliberately distinct for all three records -- order_by "an_integer"
		// alone must then be unambiguous, without needing a title tie-break whose result would
		// depend on the test database's collation (e.g. "and" vs "Apples")
		$apples = TestTable::CreateNewRecord([
			"title" => "Apples",
			"an_integer" => 10,
		]);

		$and = TestTable::CreateNewRecord([
			"title" => "and",
			"an_integer" => 20,
		]);

		$oranges = TestTable::CreateNewRecord([
			"title" => "Oranges",
			"an_integer" => 30,
		]);

		// -- iterating with a small batch size, caching explicitly enabled on the finder
		// (TABLERECORD_USE_CACHE_BY_DEFAULT is false in this test environment; the real
		// application sets it to !TEST, i.e. true outside of tests)
		$finder = TestTable::Finder([
			"order_by" => "an_integer",
			"limit" => null,
			"use_cache" => true,
		]);

		Cache::Clear("TestTable");
		$titles = [];
		foreach($finder->iterateRecords(["batch_size" => 1]) as $record){
			$titles[] = $record->getTitle();
			// with a batch_size of 1, the cache never holds more than the one record just yielded
			$this->assertTrue(count(Cache::CachedIds("TestTable"))<=1);
		}
		$this->assertEquals(["Apples","and","Oranges"],$titles);
		// once the whole resultset has been iterated, nothing is left in the cache
		$this->assertEquals([],Cache::CachedIds("TestTable"));

		// -- a finder matching many records must not have its finder-construction-time
		// Cache::Prepare() (done for *all* matched ids, see TableRecord::_Finder()) make
		// iterateRecords() read much more than one batch at a time
		$finder = TestTable::Finder([
			"order_by" => "an_integer",
			"limit" => null,
			"use_cache" => true,
		]);
		$this->assertTrue(count(Cache::CachedIds("TestTable"))>1); // poisoned by _Finder() with all 3 ids

		$titles = [];
		foreach($finder->iterateRecords(["batch_size" => 1]) as $record){
			$titles[] = $record->getTitle();
			$this->assertTrue(count(Cache::CachedIds("TestTable"))<=1);
		}
		$this->assertEquals(["Apples","and","Oranges"],$titles);
		$this->assertEquals([],Cache::CachedIds("TestTable"));

		// -- iterateRecords() only reclaims the prepare-queue for its own matched ids; an
		// unrelated, already-cached record of the same class (not matched by this finder)
		// must survive the iteration
		$grapes = TestTable::CreateNewRecord([
			"title" => "Grapes",
			"an_integer" => 99,
		]);
		Cache::Get("TestTable",$grapes); // cache it, unrelated to the finder below
		$this->assertEquals([$grapes->getId() => $grapes->getId()],Cache::CachedIds("TestTable"));

		$finder = TestTable::Finder([
			"conditions" => ["title != 'Grapes'"],
			"order_by" => "an_integer",
			"limit" => null,
			"use_cache" => true,
		]);

		$titles = [];
		foreach($finder->iterateRecords(["batch_size" => 1]) as $record){
			$titles[] = $record->getTitle();
		}
		$this->assertEquals(["Apples","and","Oranges"],$titles);
		// grapes was never matched by this finder, so it must still be cached
		$this->assertEquals([$grapes->getId() => $grapes->getId()],Cache::CachedIds("TestTable"));

		Cache::Clear("TestTable");

		// -- iterating with caching disabled on the finder; behaves the same
		$finder = TestTable::Finder([
			"conditions" => ["title != 'Grapes'"],
			"order_by" => "an_integer",
			"limit" => null,
			"use_cache" => false,
		]);

		$titles = [];
		foreach($finder->iterateRecords() as $record){
			$titles[] = $record->getTitle();
		}
		$this->assertEquals(["Apples","and","Oranges"],$titles);

		// -- iterating over an empty resultset
		$finder = TestTable::Finder(["conditions" => ["1=0"]]);
		$titles = [];
		foreach($finder->iterateRecords() as $record){
			$titles[] = $record->getTitle();
		}
		$this->assertEquals([],$titles);
	}

	function test_iterate_records_reuses_already_fetched_ids(){
		TestTable::CreateNewRecord(["title" => "Apples","an_integer" => 10]);
		TestTable::CreateNewRecord(["title" => "and","an_integer" => 20]);
		TestTable::CreateNewRecord(["title" => "Oranges","an_integer" => 30]);
		$dbmole = TestTable::GetDbmole();

		// use_cache=>true: TableRecord::_Finder() already ran the id-query itself (for its own
		// eager Cache::Prepare()) -- iterateRecords() must reuse that result, not run it again
		$queries_executed = $dbmole->getQueriesExecuted();
		$finder = TestTable::Finder(["order_by" => "an_integer","limit" => null,"use_cache" => true]);
		$queries_after_construction = $dbmole->getQueriesExecuted();
		$this->assertEquals($queries_executed+1,$queries_after_construction); // just _Finder()'s own id-query

		foreach($finder->iterateRecords(["batch_size" => 1]) as $record){}
		$this->assertEquals($queries_after_construction+3,$dbmole->getQueriesExecuted()); // 3 records, 1 query/record, no extra id-query

		Cache::Clear("TestTable");

		// use_cache=>false: _Finder() never fetches the ids up front, so iterateRecords() must
		// still stream them (no id-query has run yet at construction time)
		$queries_executed = $dbmole->getQueriesExecuted();
		$finder = TestTable::Finder(["order_by" => "an_integer","limit" => null,"use_cache" => false]);
		$this->assertEquals($queries_executed,$dbmole->getQueriesExecuted()); // nothing executed yet

		foreach($finder->iterateRecords() as $record){}
		$this->assertEquals($queries_executed+2,$dbmole->getQueriesExecuted()); // 1 id-query + 1 batched record read
	}

	function test_empty_finder(){
		$finder = TableRecord::EmptyFinder();

		$records = $finder->getRecords();
		$this->assertEquals(0,count($records));
		$this->assertEquals(0,count($finder));
		$this->assertEquals(0,$finder->getTotalAmount());
		$this->assertEquals(0,$finder->getRecordsDisplayed());
		$this->assertEquals(false,$finder->hardLimitReached());

		$this->assertEquals(true,$finder->atBeginning());
		$this->assertEquals(true,$finder->atEnd());
		$this->assertEquals(null,$finder->getPrevOffset());
		$this->assertEquals(null,$finder->getNextOffset());
	}

	function test_getQueryData() {
		$apples = TestTable::CreateNewRecord([
			"id" => 10,
			"title" => "Apples",
			"an_integer" => 10,
		]);

		$and = TestTable::CreateNewRecord([
			"id" => 20,
			"title" => "and",
			"an_integer" => 15,
		]);

		$oranges = TestTable::CreateNewRecord([
			"id" => 30,
			"title" => "Oranges",
			"an_integer" => 30,
		]);

		// an usual finder
		$finder = TestTable::Finder(["order_by" => "id"]);
		$this->assertEquals([
			10 => ["id" => 10],
			20 => ["id" => 20],
			30 => ["id" => 30],
		],$finder->getQueryData());

		// a finder with a special query with more fields in the select statement
		$finder = TestTable::Finder(["query" => "SELECT id, title, an_integer FROM test_table", "order_by" => "id"]);
		$this->assertEquals($finder->getRecordsCount(), 3);
		$expect = [
			10 => ['id' => '10', 'title' => 'Apples', 'an_integer' => '10'],
			20 => ['id' => '20', 'title' => 'and', 'an_integer' => '15'],
			30 => ['id' => '30', 'title' => 'Oranges', 'an_integer' => '30']
		];

		foreach($finder as $r) {
			$this->assertEquals($finder->getQueryData($r), $expect[$r->getId()]);
			$this->assertEquals($finder->getQueryData($r->getId()), $expect[$r->getId()]);
			$this->assertEquals($finder->getQueryData($r,'an_integer'), $expect[$r->getId()]['an_integer']);
		}
		$this->assertEquals($finder->getQueryData(), $expect);
	}
}

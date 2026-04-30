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

	function test_empty_finder(){
		$finder = TableRecord::EmptyFinder();

		$records = $finder->getRecords();
		$this->assertEquals(0,count($records));
		$this->assertEquals(0,count($finder));
		$this->assertEquals(0,$finder->getTotalAmount());
		$this->assertEquals(0,$finder->getRecordsDisplayed());

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

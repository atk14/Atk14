<?
class tc_inobj_table_record_finder extends tc_base{
	function test(){
		$apples = inobj_TestTable::CreateNewRecord(array(
			"title" => "Apples",
			"an_integer" => 10,
		));

		$and = inobj_TestTable::CreateNewRecord(array(
			"title" => "and",
			"an_integer" => 10,
		));

		$oranges = inobj_TestTable::CreateNewRecord(array(
			"title" => "Oranges",
			"an_integer" => 30,
		));

		$finder = inobj_TestTable::Finder(array(
			"order_by" => "an_integer",
			"limit" => 1,
		));

		$records = $finder->getRecords();
		$this->assertEquals(3,$finder->getTotalAmount());
		$this->assertEquals(1,sizeof($records));
		$this->assertEquals(1,sizeof($finder));
		$this->assertEquals("Apples",$finder[0]->getTitle());
		$this->assertEquals("Apples",$records[0]->getTitle());

		// --
		$finder = inobj_TestTable::Finder(array(
			"order_by" => "an_integer",
			"limit" => 2,
			"offset" => 1,
		));

		$records = $finder->getRecords();
		$this->assertEquals(3,$finder->getTotalAmount());
		$this->assertEquals(2,sizeof($records));
		$this->assertEquals(2,sizeof($finder));

		$this->assertEquals("and",$records[0]->getTitle());
		$this->assertEquals("and",$finder[0]->getTitle());

		$this->assertEquals("Oranges",$records[1]->getTitle());
		$this->assertEquals("Oranges",$finder[1]->getTitle());

		// --
		$finder = inobj_TestTable::Finder("an_integer",10,array("order_by" => "UPPER(title) DESC"));

		$records = $finder->getRecords();
		$this->assertEquals(2,sizeof($records));
		$this->assertEquals($apples->getId(),$records[0]->getId());
		$this->assertEquals($and->getId(),$records[1]->getId());

		// --
		$finder = inobj_TestTable::Finder("an_integer",10,"title","Apples",array("order_by" => "UPPER(title) DESC"));

		$records = $finder->getRecords();
		$this->assertEquals(1,sizeof($records));
		$this->assertEquals($apples->getId(),$records[0]->getId());
	}

	function test_empty_finder(){
		$finder = TableRecord::EmptyFinder();

		$records = $finder->getRecords();
		$this->assertEquals(0,sizeof($records));
		$this->assertEquals(0,sizeof($finder));
		$this->assertEquals(0,$finder->getTotalAmount());
	}
}

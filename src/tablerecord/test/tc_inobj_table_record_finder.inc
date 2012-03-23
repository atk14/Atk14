<?
class tc_inobj_table_record_finder extends tc_base{
	function test(){
		inobj_TestTable::CreateNewRecord(array(
			"title" => "Apples",
			"an_integer" => 10,
		));

		inobj_TestTable::CreateNewRecord(array(
			"title" => "and",
			"an_integer" => 10,
		));

		inobj_TestTable::CreateNewRecord(array(
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
	}

	function test_empty_finder(){
		$finder = TableRecord::EmptyFinder();

		$records = $finder->getRecords();
		$this->assertEquals(0,sizeof($records));
		$this->assertEquals(0,sizeof($finder));
		$this->assertEquals(0,$finder->getTotalAmount());
	}
}

<?php
class tc_lister extends tc_base{
	function test(){
		global $dbmole;

		$dbmole->begin();

		$this->article = Article::CreateNewRecord(array(
			"title" => "Christ's Sheep",
			"body" => "'My sheep hear My voice'\nChrist did say, 'and I know them\nand they follow Me'",
		));

		$john = Author::CreateNewRecord(array(
			"name" => "John",
		));

		$peter = Author::CreateNewRecord(array(
			"name" => "Peter",
		));

		$paul = Author::CreateNewRecord(array(
			"name" => "Paul",
		));

		$lister = $this->article->getAuthorsLister();

		$this->_test_authors(array());

		$lister->append($john);
		$this->_test_authors(array($john));
		$this->assertTrue($lister->contains($john));
		$this->assertTrue($lister->contains($john->getId()));
		$this->assertFalse($lister->contains($peter));
		$this->assertFalse($lister->contains(null));

		$lister->append($peter);
		$this->_test_authors(array($john,$peter));
		$this->assertTrue($lister->contains($john));
		$this->assertTrue($lister->contains($peter));

		$lister->prepend($paul);
		$this->_test_authors(array($paul,$john,$peter));

		$items = $lister->getItems();
		// move John to the begin
		$items[1]->setRank(0);
		$this->_test_authors(array($john,$paul,$peter));

		$lister->setRecordRank($john,1);
		$this->_test_authors(array($paul,$john,$peter));

		$lister->setRecordRank($john,1);
		$this->_test_authors(array($paul,$john,$peter));

		$lister->setRecordRank($john,2);
		$this->_test_authors(array($paul,$peter,$john));

		$lister->setRecordRank($john,0);
		$this->_test_authors(array($john,$paul,$peter));

		$lister->remove($john);
		$this->_test_authors(array($paul,$peter));

		$dbmole->rollback();
	}

	function _test_authors($expected_authors){
		$authors = $this->article->getAuthors();
		$this->assertEquals(sizeof($expected_authors),sizeof($authors));
		for($i=0;$i<sizeof($authors);$i++){
			$this->assertEquals($expected_authors[$i]->getId(),$authors[$i]->getId());
		}

		$lister = $this->article->getAuthorsLister();
		$items = $lister->getItems();
		for($i=0;$i<sizeof($authors);$i++){
			$this->assertEquals($i,$items[$i]->getRank());
			$this->assertEquals($expected_authors[$i]->getId(),$items[$i]->getRecordId());
		}

		// getRecords(), getRecordIds()
		$records = $lister->getRecords();
		$record_ids = $lister->getRecordIds();

		$this->assertEquals(sizeof($records),sizeof($record_ids));

		for($i=0;$i<sizeof($expected_authors);$i++){
			$this->assertEquals($expected_authors[$i]->getId(),$records[$i]->getId());
			$this->assertEquals($expected_authors[$i]->getId(),$record_ids[$i]);
		}
		
	}
}

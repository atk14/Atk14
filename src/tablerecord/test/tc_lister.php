<?php
class DbException extends Exception {};
function dbehandler($msg){
	throw new DbException('DBERROR');
}

class TcLister extends TcBase{
	function exceptException($exc, $fce) {
		$raised = false;
		try {
			//Postgres rve, misto "slusneho" jen vyhozeni vyjimky
			@$fce();
		} catch (Exception $e) {
			if(!($e instanceof $exc)) {
				throw $e;
			}
			$raised = true;
		}
		$this->assertTrue($raised, "There should be exception $exc raised.");
	}

	function exceptDbError($fce) {
		$this->dbmole->setErrorHandler('dbehandler');
		$this->dbmole->doQuery('savepoint sp_dbmole_errorrr');
		$this->exceptException('DbException', $fce);
		$this->dbmole->setErrorHandler(null);
		$this->dbmole->doQuery('ROLLBACK TO SAVEPOINT sp_dbmole_errorrr; RELEASE sp_dbmole_errorrr');
		$this->dbmole->_ErrorRaised = false;
	}

	function test(){
		$this->article = Article::CreateNewRecord(array(
			"title" => "Christ's Sheep",
			"body" => "'My sheep hear My voice'\nChrist did say, 'and I know them\nand they follow Me'",
		));

		$this->article2 = Article::CreateNewRecord(array(
			"title" => "John 8:36",
			"body" => "If the Son therefore shall make you free, ye shall be free indeed.",
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

		$tony = Author::CreateNewRecord(array(
			"name" => "Tony",
		));


		//test unikaniho vlozeni
		$rjohn = Redactor::CreateNewRecord(array(
			"name" => "John",
		));

		$rpeter = Redactor::CreateNewRecord(array(
			"name" => "Peter",
		));
		$rlister = $this->article->getLister('redactors');
		//tady to nesmi hodit vyjimku
		$this->_setRecords($rlister,array($rjohn, $rpeter));
		$this->_setRecords($rlister,array($rpeter, $rjohn));
		$this->_setRecords($rlister,array($rjohn));
		$this->_setRecords($rlister,array($rjohn, $rpeter));
		$this->_setRecords($rlister,array($rpeter));
		//a tady to pro kontrolu musi
		$this->exceptDbError(function() use($rlister, $rpeter) { $rlister->setRecords(array($rpeter, $rpeter)); });


		$lister2 = $this->article2->getAuthorsLister();
		$lister2->append($john);

		# test ArrayAccess behavior of the lister
		$lister2[] = $tony;
		$this->assertTrue($lister2->contains($john));
		$this->assertTrue($lister2->contains($tony));
		$this->assertEquals(2, sizeof($lister2));

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

		$lister->prepend($tony);
		$this->_test_authors(array($tony,$paul,$john,$peter));

		$lister->remove($tony);
		$this->_test_authors(array($paul,$john,$peter));

		// testing setRank
		$items = $lister->getItems();
		$items[0]->setRank(1);
		$this->_test_authors(array($john,$paul,$peter),true);
		$this->assertEquals(1,$lister->getRecordRank($paul));

		$items = $lister->getItems();
		$items[2]->setRank(1);
		$this->_test_authors(array($john,$peter,$paul),true);
		$this->assertEquals(1,$lister->getRecordRank($peter));

		$items = $lister->getItems();
		$items[2]->setRank(-100);
		$this->_test_authors(array($paul,$john,$peter),true);
		$this->assertEquals(0,$lister->getRecordRank($paul));

		$items = $lister->getItems();
		$items[1]->setRank(+100);
		$this->_test_authors(array($paul,$peter,$john),true);
		$this->assertEquals(2,$lister->getRecordRank($john));

		$items = $lister->getItems();
		$items[2]->setRank(0);
		$this->_test_authors(array($john,$paul,$peter),true);
		$this->assertEquals(0,$lister->getRecordRank($john));

		$lister->setRecordRank($john,1);
		$this->_test_authors(array($paul,$john,$peter),true);
		$this->assertEquals(1,$lister->getRecordRank($john));

		$lister->setRecordRank($john,1);
		$this->_test_authors(array($paul,$john,$peter),true);

		$lister->setRecordRank($john,2);
		$this->_test_authors(array($paul,$peter,$john),true);
		$this->assertEquals(2,$lister->getRecordRank($john));

		$lister->setRecordRank($john,0);
		$this->_test_authors(array($john,$paul,$peter),true);
		$this->assertEquals(0,$lister->getRecordRank($john));

		$lister->setRecordRank($john,10);
		$this->_test_authors(array($paul,$peter,$john),true);
		$this->assertEquals(2,$lister->getRecordRank($john));

		$lister->setRecordRank($john,-10);
		$this->_test_authors(array($john,$paul,$peter),true);
		$this->assertEquals(0,$lister->getRecordRank($john));

		$lister->remove($john);
		$this->_test_authors(array($paul,$peter));

		// non-unique behaviour
		$lister->append($john);
		$lister->append($john);

		$this->_test_authors(array($paul,$peter,$john,$john));

		// setRecords
		$lister->setRecords(array($john,$peter));
		$this->_test_authors(array($john,$peter));

		$lister->setRecords(array());
		$this->_test_authors(array());

		$lister->setRecords(array($paul,$john));
		$this->_test_authors(array($paul,$john));

		$lister->setRecords(array($peter,$paul));
		$this->_test_authors(array($peter,$paul));

		$lister->setRecords(array($john,$peter,$paul));
		$this->_test_authors(array($john,$peter,$paul));

		# test ArrayAccess behavior of the Lister
		$lister[1] = $tony;
		$this->_test_authors(array($john, $tony, $paul));
		$lister[2] = $peter;
		$this->_test_authors(array($john, $tony, $peter));
		$lister[3] = $paul;
		$this->_test_authors(array($john, $tony, $peter, $paul));
		# test offsetUnset
		unset($lister[2]);
		$this->_test_authors(array($john, $tony, $paul));
	}

	function _test_authors($expected_authors,$test_saved_ranks = false){
		$authors = $this->article->getAuthors();

		$this->assertEquals(sizeof($expected_authors),sizeof($authors));
		for($i=0;$i<sizeof($authors);$i++){
			$this->assertEquals($expected_authors[$i]->getId(),$authors[$i]->getId());
		}

		$lister = $this->article->getAuthorsLister();
		$items = $lister->getItems();
		# test lister behaves as countable
		$this->assertEquals(sizeof($items), sizeof($lister));

		for($i=0;$i<sizeof($authors);$i++){
			$this->assertEquals($i,$items[$i]->getRank());
			if($test_saved_ranks){
				$this->assertEquals($i,$items[$i]->_getSavedRank());
			}
			$this->assertEquals($expected_authors[$i]->getId(),$items[$i]->getRecordId());

			# test that lister behaves the same as array
			$this->assertTrue(is_a($lister[$i],"Author"));
			$this->assertEquals($expected_authors[$i]->getId(),$lister[$i]->getId());
		}

		foreach($lister as $key => $record) {
			$this->assertTrue(is_a($record,"Author"));
			$this->assertEquals($expected_authors[$key]->getId(), $record->getId());

		}

		// getRecords(), getRecordIds()
		$records = $lister->getRecords();
		$record_ids = $lister->getRecordIds();
		$ids = $lister->getIds();

		$this->assertEquals(sizeof($records),sizeof($record_ids));
		$this->assertEquals(sizeof($records),sizeof($ids));

		for($i=0;$i<sizeof($expected_authors);$i++){
			$this->assertEquals($expected_authors[$i]->getId(),$records[$i]->getId());
			$this->assertEquals($expected_authors[$i]->getId(),$record_ids[$i]);

			$this->assertEquals($record_ids[$i],$this->dbmole->selectInt("SELECT author_id FROM article_authors WHERE id=:id",array(":id" => $ids[$i])));
		}

		//

		$authors2 = $this->article2->getAuthors();
		$this->assertEquals(2,sizeof($authors2));
		$this->assertEquals("John",$authors2[0]->getName());
	}

	function _setRecords($lister,$objects){
		$lister->setRecords($objects);

		$current_objects = $lister->getRecords();

		$this->assertEquals(sizeof($objects),sizeof($current_objects));
		foreach($objects as $o){
			$current_o = array_shift($current_objects);
			$this->assertEquals($o->getId(),$current_o->getId());
		}
	}
}

<?php
class TcTableRecord extends TcBase{

	function test_record_creation(){
		$this->_empty_test_table();

		$record = TestTable::CreateNewRecord(array());

		$this->assertEquals("test_table_id_seq",$record->getSequenceName());
		$this->assertEquals($this->dbmole->SelectSingleValue("SELECT CURRVAL('test_table_id_seq')","integer"),$record->getId());
		$this->assertNull($record->getValue("title"));
		$this->assertNull($record->getValue("price"));
		$this->assertNull($record->getValue("an_integer"));

		$record = TestTable::CreateNewRecord(array(
			"title" => "test",
			"price" => null,
			"an_integer" => 10
		));
		$this->assertEquals($this->dbmole->SelectSingleValue("SELECT CURRVAL('test_table_id_seq')","integer"),$record->getId());
		$this->assertEquals("test",$record->getValue("title"));
		$this->assertNull($record->getValue("price"));
		$this->assertEquals(10,$record->getValue("an_integer"));

		$this->assertEquals(true,$record->hasKey("title"));
		$this->assertEquals(false,$record->hasKey("subtitle"));
	}

	function test_exports(){
		$dbmole = $this->dbmole;

		$this->_prepare_test_record();
		$rec = TestTable::GetInstanceById(2);

		$q_cnt = $dbmole->getQueriesExecuted();

		$values = $rec->getValues();
		$array = $rec->toArray();

		$this->assertEquals($q_cnt,$dbmole->getQueriesExecuted());

		$this->assertEquals($values,$array);

		$values = $rec->getValues(array("return_id" => false));

		$this->assertEquals(false,isset($values["id"]));
		$this->assertEquals(true,isset($array["id"]));

		// Article has do_not_read_values to ["body"]

		$article = Article::CreateNewRecord(array("title" => "La Title", "body" => "La Body"));
		$article = Article::GetInstanceById($article->getId());

		$this->assertNotContains("body",$dbmole->getQuery());

		$q_cnt = $dbmole->getQueriesExecuted();

		$array = $article->toArray();
		$this->assertEquals("La Body",$array["body"]);

		$this->assertEquals($q_cnt+1,$dbmole->getQueriesExecuted());
		$this->assertContains("body",$dbmole->getQuery());

		$array = $article->toArray();

		$this->assertEquals($q_cnt+1,$dbmole->getQueriesExecuted());
	}

	function test_use_cache(){
		$dbmole = TestTable::GetDbmole();

		$this->_empty_test_table();
		$this->_prepare_test_record();

		$rec = TestTable::GetInstanceById(2);
		$c_rec = Cache::Get("TestTable",2); // just caching
		$c_rec->cached = true;

		// not using caching
		$qe = $dbmole->getQueriesExecuted();
		$rec = TestTable::GetInstanceById(2);
		$this->assertFalse(isset($rec->cached));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertTrue($qe_2==$qe+1);

		// using cache
		$qe = $dbmole->getQueriesExecuted();
		$rec = TestTable::GetInstanceById(2,array("use_cache" => true));
		$this->assertTrue(isset($rec->cached));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe,$qe_2);

		// get instances by array; not using cache
		$qe = $dbmole->getQueriesExecuted();
		$recs = TestTable::GetInstanceById(array(2));
		$this->assertFalse(isset($recs[0]->cached));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe+1,$qe_2);

		// get instances by array; using cache
		$qe = $dbmole->getQueriesExecuted();
		$recs = TestTable::GetInstanceById(array(2),array("use_cache" => true));
		$this->assertTrue(isset($recs[0]->cached));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe,$qe_2);

		// FindAll, not using cache
		$qe = $dbmole->getQueriesExecuted();
		$recs = TestTable::FindAll("title","titulek");
		$this->assertFalse(isset($recs[0]->cached));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe+2,$qe_2); // one query for searching ids, second query for getting objects data

		// FindAll, using cache
		$qe = $dbmole->getQueriesExecuted();
		$recs = TestTable::FindAll("title","titulek",array("use_cache" => true));
		$this->assertTrue(isset($recs[0]->cached));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe+1,$qe_2); // one query for searching ids

		// FindFirst, not using cache
		$qe = $dbmole->getQueriesExecuted();
		$rec = TestTable::FindFirst("title","titulek");
		$this->assertFalse(isset($rec->cached));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe+2,$qe_2); // one query for searching ids, second query for getting objects data

		// FindFirst, using cache
		$qe = $dbmole->getQueriesExecuted();
		$rec = TestTable::FindFirst("title","titulek",array("use_cache" => true));
		$this->assertTrue(isset($rec->cached));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe+1,$qe_2); // one query for searching ids

		// Finder, not using cache
		$qe = $dbmole->getQueriesExecuted();
		$finder = TestTable::Finder(array("conditions" => "title='titulek'"));
		$recs = $finder->getRecords();
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe+2,$qe_2); // one query for searching ids, second query for getting objects data

		// Finder, using cache
		$qe = $dbmole->getQueriesExecuted();
		$finder = TestTable::Finder(array("conditions" => "title='titulek'", "use_cache" => true));
		$recs = $finder->getRecords();
		$this->assertTrue(isset($recs[0]->cached));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe+1,$qe_2); // one query for searching ids

		// eceateNewRecord; not using cached while creating record
		$qe = $dbmole->getQueriesExecuted();
		$rec = TestTable::CreateNewRecord(array(
			"id" => 221,
			"title" => "La test"
		));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe+2,$qe_2);
		// --
		$rec = TestTable::GetInstanceById($rec->getId(),array("use_cache" => true));
		$qe_3 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe_2+1,$qe_3);

		// CreateNewRecord; using cached while creating record
		$qe = $dbmole->getQueriesExecuted();

		$this->assertEquals(null,Cache::Get("TestTable",223));
		$this->assertEquals($qe+1,$dbmole->getQueriesExecuted());

		$this->assertEquals(null,Cache::Get("TestTable",223));
		$this->assertEquals($qe+1,$dbmole->getQueriesExecuted());

		$rec = TestTable::CreateNewRecord(array(
			"id" => 223,
			"title" => "La cache"
		),array("use_cache" => true));
		$qe_2 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe+3,$qe_2);
		// --
		$rec = TestTable::GetInstanceById($rec->getId(),array("use_cache" => true));
		$qe_3 = $dbmole->getQueriesExecuted();
		$this->assertEquals($qe_2,$qe_3);
	}

	function _prepare_test_record(){
		$this->dbmole->doQuery("
			INSERT INTO test_table (
				id,
				title,
				an_integer,
				a_big_integer,
				price,
				text,
				create_date,
				create_time,
				flag
			) VALUES(
				2,
				'titulek',
				21,
				9223372036854775807,
				17.0,
				'textik',
				'2001-12-12 12:00:00',
				'2001-12-12 12:00:00',
				TRUE
			)
		");
	}

	function test_test_table(){
		$this->_empty_test_table();
		$this->_prepare_test_record();


		//var_dump($this->dbmole->selectRows("SELECT * FROM test_table"));

		$record = TestTable::GetInstanceById(2);

		//var_dump($record->toArray()); exit;

		$this->assertEquals(2,$record->getId());
		$this->assertTrue(is_int($record->getId()));
		$this->assertEquals("TestTable#2",$record->toString());
		$this->assertEquals("TestTable#2","$record");

		$this->assertEquals("titulek",$record->getValue("title"));

		$this->assertEquals(21,$record->getValue("an_integer"));
		$this->assertTrue(is_int($record->getValue("an_integer")));
		$this->assertEquals(9223372036854775807,$record->getValue("a_big_integer"));
		$this->assertTrue(is_int($record->getValue("a_big_integer")));

		$this->assertEquals(17.0,$record->getValue("price"));
		$this->assertTrue(is_float($record->getValue("price")));

		$this->assertEquals("textik",$record->getValue("text"));
		$this->assertEquals("2001-12-12",$record->getValue("create_date"));
		$this->assertEquals("2001-12-12 12:00:00",$record->getValue("create_time"));

		// testovani nastavoani vlastnosti
		// volani setValue()
		$this->assertTrue($record->setValue("price",15.6));
		$record = TestTable::GetInstanceById(2);
		$this->assertEquals(15.6,$record->getValue("price"));

		$this->assertTrue($record->setValue("price",null));
		$record = TestTable::GetInstanceById(2);
		$this->assertNull($record->getValue("price"));

		// volani setValues()
		$this->assertTrue($record->setValues(array("price" => 13.4,"an_integer" => 20)));
		$record = TestTable::GetInstanceById(2);
		$this->assertEquals(13.4,$record->getValue("price"));
		$this->assertEquals(20,$record->getValue("an_integer"));

		$this->assertTrue($record->setValues(array("price" => -12,"an_integer" => null)));
		$record = TestTable::GetInstanceById(2);
		$this->assertEquals(-12.0,$record->getValue("price"));
		$this->assertNull($record->getValue("an_integer"));

		$this->assertTrue($record->setValue("title","ahoj"));
		$record = TestTable::GetInstanceById(2);
		$this->assertEquals("ahoj",$record->getValue("title"));

		$this->assertTrue($record->setValue("title",""));
		$this->assertEquals("",$record->getValue("title"));
		$record = TestTable::GetInstanceById(2);
		$this->assertEquals("",$record->getValue("title"));

		// boolean
		$this->assertEquals(true,$record->getValue("flag"));
		$this->assertEquals('t',$this->dbmole->selectString("SELECT flag FROM test_table WHERE id=2"));

		$record->setValue("flag",false);
		$this->assertEquals(false,$record->getValue("flag"));
		$this->assertEquals('f',$this->dbmole->selectString("SELECT flag FROM test_table WHERE id=2"));

		$record->setValue("flag",null);
		$this->assertEquals(null,$record->getValue("flag"));
		$this->assertEquals(null,$this->dbmole->selectString("SELECT flag FROM test_table WHERE id=2"));
	}

	function test_getting_multiple_values(){
		$rec = TestTable::CreateNewRecord(array(
			"title" => "La Fabrique",
			"price" => 200
		));

		$this->assertEquals(array("La Fabrique", 200.0),$rec->getValue(array("title","price")));
		$this->assertEquals(array("title" => "La Fabrique", "price" => 200.0),$rec->getValue(array("title" => "title", "price" => "price")));
		$this->assertEquals(array("a" => "La Fabrique", "b" => 200.0),$rec->getValue(array("a" => "title", "b" => "price")));
	}

	function test_converting_objects_into_scalars(){
		// __toString
		$title = new StringLike("Hello World!");
		$rec = TestTable::CreateNewRecord(array(
			"title" => $title
		));
		$this->assertEquals("Hello World!",$rec->getTitle());

		// toString
		$title = new StringMuchLike("Hello World from Prague!");
		$rec = TestTable::CreateNewRecord(array(
			"title" => $title
		));
		$this->assertEquals("Hello World from Prague!",$rec->getTitle());
		$this->assertEquals("Hello World from Prague! (__toString)","$title"); // __toString

		// toId
		$integer = new IntLike(133);
		$rec = TestTable::CreateNewRecord(array(
			"an_integer" => $integer
		));
		$this->assertEquals(133,$rec->getAnInteger());
	}

	function test_validates_updating_of_fields(){
		// vsechno, co menime, bude meneno
		$record = TestTable::CreateNewRecord(array(
			"title" => "nazev",
			"price" => 100,
			"an_integer" => 200
		));
		$record->setValues(array(
			"title" => "novy nazev",
			"price" => 101,
			"an_integer" => 201
		),array(
			"validates_updating_of_fields" => array("title","price","an_integer")
		));
		$this->assertEquals("novy nazev",$record->getValue("title"));
		$this->assertEquals(101.0,$record->getValue("price"));
		$this->assertEquals(201,$record->getValue("an_integer"));

		// zde se zmeni pouze 2 pole
		$record = TestTable::CreateNewRecord(array(
			"title" => "nazev",
			"price" => 100,
			"an_integer" => 200
		));
		$record->setValues(array(
			"title" => "novy nazev",
			"price" => 101,
			"an_integer" => 201
		),array(
			"validates_updating_of_fields" => array("title","price")
		));
		$this->assertEquals("novy nazev",$record->getValue("title"));
		$this->assertEquals((float)101,$record->getValue("price"));
		$this->assertEquals(200,$record->getValue("an_integer"));

		// zde se nic nesmi zmenit
		$record = TestTable::CreateNewRecord(array(
			"title" => "nazev",
			"price" => 100,
			"an_integer" => 200
		));
		$record->setValues(array(
			"title" => "novy nazev",
			"price" => 101,
			"an_integer" => 201
		),array(
			"validates_updating_of_fields" => array("text","create_date")
		));
		$this->assertEquals("nazev",$record->getValue("title"));
		$this->assertEquals(100.0,$record->getValue("price"));
		$this->assertEquals(200,$record->getValue("an_integer"));

		// nastaveni null hodnot
		$record = TestTable::CreateNewRecord(array(
			"title" => "nazev",
			"price" => 100,
			"an_integer" => 200
		));
		$record->setValues(array(
			"title" => null,
			"price" => null,
			"an_integer" => null
		),array(
			"validates_updating_of_fields" => array("title","an_integer")
		));
		$this->assertEquals(null,$record->getValue("title"));
		$this->assertEquals(100.0,$record->getValue("price"));
		$this->assertEquals(null,$record->getValue("an_integer"));
	}

	function test_validates_inserting_of_fields(){
		$record = TestTable::CreateNewRecord(array(
			"title" => "nazev",
			"price" => 100,
			"an_integer" => 200
		),array(
			"validates_inserting_of_fields" => array("title","price","an_integer"),
		));
		$this->assertEquals("nazev",$record->getValue("title"));
		$this->assertEquals(100.0,$record->getValue("price"));
		$this->assertEquals(200,$record->getValue("an_integer"));

		$record = TestTable::CreateNewRecord(array(
			"title" => "nazev",
			"price" => 100,
			"an_integer" => 200
		),array(
			"validates_inserting_of_fields" => array("title"),
		));
		$this->assertEquals("nazev",$record->getValue("title"));
		$this->assertEquals(null,$record->getValue("price"));
		$this->assertEquals(null,$record->getValue("an_integer"));

		$record = TestTable::CreateNewRecord(array(
			"title" => "nazev",
			"price" => 100,
			"an_integer" => 200
		),array(
			"validates_inserting_of_fields" => array("create_date","text"),
		));
		$this->assertEquals(null,$record->getValue("title"));
		$this->assertEquals(null,$record->getValue("price"));
		$this->assertEquals(null,$record->getValue("an_integer"));

		$record = TestTable::CreateNewRecord(array(
			"text" => "texticek",
		),array(
			"validates_inserting_of_fields" => array("text"),
		));
		$this->assertEquals("texticek",$record->getValue("text"));

		$record = TestTable::CreateNewRecord(array(
			"text" => "texticek",
		),array(
			"validates_inserting_of_fields" => array("an_integer"),
		));
		$this->assertEquals(null,$record->getValue("text"));
	}

	function test_do_not_escape(){
		$now = $this->dbmole->SelectSingleValue("SELECT CAST(NOW() AS DATE)");
		$before_2_days = $this->dbmole->SelectSingleValue("SELECT CAST((NOW() - INTERVAL '2 days') AS DATE)");

		$record = TestTable::CreateNewRecord(array(
			"create_date" => $now
		));

		$record->setValue("create_date",null);
		$this->assertNull($record->getValue("create_date"));

		$record->setValue("create_date","NOW()",array("do_not_escape" => true));
		$this->assertEquals($now,$record->getValue("create_date"));

		$record->setValue("create_date",null);

		$record->setValues(array("create_date" => "NOW()"),array("do_not_escape" => array("create_date")));
		$this->assertEquals($now,$record->getValue("create_date"));

		$record->setValues(array("create_date" => "NULL"),array("do_not_escape" => "create_date"));
		$this->assertNull($record->getValue("create_date"));

		$record->setValues(array("create_date" => "NOW()"),array("do_not_escape" => "create_date"));
		$this->assertEquals($now,$record->getValue("create_date"));

		$record = TestTable::CreateNewRecord(array(
			"create_date" => "(NOW() - INTERVAL '2 days')"
		),array("do_not_escape" => array("create_date")));
		$this->assertEquals($before_2_days,$record->getValue("create_date"));

		$record = TestTable::CreateNewRecord(array(
			"create_date" => "(NOW() - INTERVAL '2 days')"
		),array("do_not_escape" => "create_date"));
		$this->assertEquals($before_2_days,$record->getValue("create_date"));
	}

	function test_do_not_read_values(){
		// Article has set do_not_read_values to ["body"]
		// see models/article.php

		$dbmole = Article::GetDbmole();

		$article = Article::CreateNewRecord(array(
			"title" => "La Title",
			"body" => "La Body",
		));

		$article = Article::GetInstanceById($article->getId());

		$this->assertNotContains("body",$dbmole->getQuery()); // ensure that body was not read
		$q_cnt = $dbmole->getQueriesExecuted();

		$this->assertEquals("La Title",$article->getTitle());

		$this->assertEquals($q_cnt,$dbmole->getQueriesExecuted());

		$this->assertEquals(null,$article->getImageId());

		$this->assertEquals($q_cnt,$dbmole->getQueriesExecuted());

		$this->assertEquals("La Body",$article->getBody());

		$this->assertContains("body",$dbmole->getQuery());
		$this->assertEquals($q_cnt+1,$dbmole->getQueriesExecuted());

		$this->assertEquals("La Body",$article->getBody());

		$this->assertEquals($q_cnt+1,$dbmole->getQueriesExecuted());
	}

	function test_vytvareni_vice_instanci_najednou(){
		$this->_empty_test_table();

		$record1 = $this->_vytvor_testovaci_zaznam();
		$record2 = $this->_vytvor_testovaci_zaznam();

		$id1 = $record1->getId();
		$id2 = $record2->getId();

		// int[]
		$records = TestTable::GetInstanceById(array($id2,$id1));
		$this->assertTrue(is_array($records));
		$this->assertEquals(2,sizeof($records));
		$this->assertEquals($id2,$records[0]->getId());
		$this->assertEquals($id1,$records[1]->getId());

		// obj[]
		$records = TestTable::GetInstanceById(array($record2->getId(),$record1->getId()));
		$this->assertTrue(is_array($records));
		$this->assertEquals(2,sizeof($records));
		$this->assertEquals($id2,$records[0]->getId());
		$this->assertEquals($id1,$records[1]->getId());
	
		// obj[] by objects
		$records_by_objs = TestTable::GetInstanceById(array($record2,$record1));
		$this->assertEquals($records,$records_by_objs);

		// obj[] by objects and integers
		$records_by_mixed = TestTable::GetInstanceById(array($id2,$record1));
		$this->assertEquals($records,$records_by_mixed);

		$records = TestTable::GetInstanceById(array($id1,-1000,$id2));
		$this->assertTrue(is_array($records));
		$this->assertEquals(3,sizeof($records));
		$this->assertEquals($id1,$records[0]->getId());
		$this->assertNull($records[1]);
		$this->assertEquals($id2,$records[2]->getId());
	}

	function test_get_keys(){
		$this->_empty_test_table();

		$record = $this->_vytvor_testovaci_zaznam();
		$keys = $record->getKeys();
		# je lepsi klice seradit; pri nejake uprave tabulky metoda muze vratit seznam v jinem poradi
		sort($keys);

		// overime, zde alespone jedno pole mame null...
		// protoze i nazev takoveho pole musi byt vracen...
		$this->assertTrue(is_null($record->getValue("cena")));

		//var_dump($keys); exit;
	
		$this->assertEquals(array(
			"a_big_integer",
			"an_integer",
			"binary_data",
			"binary_data2",
			"cena",
			"cena2",
			"create_date",
			"create_time",
			"flag",
			"id",
			"perex",
			"price",
			"text",
			"title",
			"znak",
		),$keys);

		$article = Article::CreateNewRecord(array());
		$this->assertEquals(array(
			"id",
			"title",
			"body",
			"image_id",
			"created_at",
			"updated_at"
		),$article->getKeys());
	}

	function test_find_all(){
		$this->_empty_test_table();

		$spring = $this->_vytvor_testovaci_zaznam(array("title" => "Spring"));
		$summer = $this->_vytvor_testovaci_zaznam(array("title" => "Summer"));
		$fall = $this->_vytvor_testovaci_zaznam(array("title" => "Fall"));
		$winter = $this->_vytvor_testovaci_zaznam(array("title" => "Winter"));

		// nekolik zpusobu zapisu conditions...
		// ... napred nenajdeme nic
		$this->assertEquals(array(),TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => "title='Monday'")));
		$this->assertEquals(array(),TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => "title=:title", "bind_ar" => array(":title" => "Monday"))));
		$this->assertEquals(array(),TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title='Monday'"))));
		$this->assertEquals(array(),TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title='Monday'"), "bind_ar" => array(":title" => "Monday"))));
		$this->assertEquals(array(),TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title" => "Monday"))));
		$this->assertEquals(array(),TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title" => array("Monday","Tuesday")))));

		// .. pak budeme nalezat Fall
		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => "title='Fall'"));
		$this->_test_fall($recs);

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => "title=:title", "bind_ar" => array(":title" => "Fall")));
		$this->_test_fall($recs);

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title='Fall'")));
		$this->_test_fall($recs);

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title=:title"), "bind_ar" => array(":title" => "Fall")));
		$this->_test_fall($recs);

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title" => "Fall")));
		$this->_test_fall($recs);

		// testovani order_by
		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => "title!='Fall'", "order_by" => "title"));
		$this->assertEquals("SpringSummerWinter",$recs[0]->getTitle().$recs[1]->getTitle().$recs[2]->getTitle());

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => "title!='Fall'", "order_by" => "title DESC"));
		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => "title='Spring' OR title='Summer' OR title='Winter'", "order_by" => "title DESC"));
		$this->assertEquals("WinterSummerSpring",$recs[0]->getTitle().$recs[1]->getTitle().$recs[2]->getTitle());

		// vyhledavani null hodnoty...
		// ... napred title s null hodnotou nemame
		$this->assertEquals(array(),TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => "title IS NULL")));
		$this->assertEquals(array(),TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title IS NULL"))));
		$this->assertEquals(array(),TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title" => null))));
		
		// ... ted jej vytvorime
		$null = $this->_vytvor_testovaci_zaznam(array("title" => null));

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => "title IS NULL"));
		$this->_test_null($recs);

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title IS NULL")));
		$this->_test_null($recs);

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title" => null)));
		$this->_test_null($recs);

		// 
		$century = $this->_vytvor_testovaci_zaznam(array("title" => "Century", "text" => null));
		$century2 = $this->_vytvor_testovaci_zaznam(array("title" => "Century", "text" => "No code"));

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title" => "Century")));
		$this->assertEquals(2,sizeof($recs));
		// defaultni trideni je podle id
		$this->assertEquals($century->getId(),$recs[0]->getId());
		$this->assertEquals($century2->getId(),$recs[1]->getId());

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title" => "Century", "text" => null)));
		$this->assertEquals(1,sizeof($recs));
		$this->assertEquals($century->getId(),$recs[0]->getId());

		$century3 = $this->_vytvor_testovaci_zaznam(array("title" => "Another Century","text" => "Uknown"));

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title='Century' OR title='Another Century'")));
		$this->assertEquals(3,sizeof($recs));

		// vyhledavani pomoci `field_name` IN (values)
		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title" => array("Century","Another Century"))));
		$this->assertEquals(3,sizeof($recs));

		// ted testujeme to, ze poskladane query musi mit na prisl. mistech zavorky:
		// ... WHERE (title='Century' OR title='Another Century') AND (text IS NULL)
		// napred spatny dotaz
		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title='Century' OR title='Another Century' AND text IS NULL")));
		$this->assertTrue(sizeof($recs)!=1);
		// ted spravny dotaz
		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title='Century' OR title='Another Century'","text IS NULL")));
		$this->assertEquals(1,sizeof($recs));
		$this->assertEquals($century->getId(),$recs[0]->getId());

		$recs = TableRecord::FindAll(array("class_name" => "TestTable", "conditions" => array("title='Century' OR title='Another Century'","text IS NOT NULL"),"order_by" => "title"));
		$this->assertEquals(2,sizeof($recs));
		$this->assertEquals($century3->getId(),$recs[0]->getId());
		$this->assertEquals($century2->getId(),$recs[1]->getId());

		// alternativni nazvy hodnot v $options
		//	 class_name -> class
		//	 conditions -> condition
		//	 bind_ar -> bind
		//	 order_by -> order
		$recs = TableRecord::FindAll(array(
			"class" => "TestTable",
			"condition" => array("title=:title1 OR title=:title2","text IS NOT NULL"),
			"bind" => array(":title1" => 'Century', ":title2" => "Another Century"),
			"order" => "title",
		));
		$this->assertEquals(2,sizeof($recs));
		$this->assertEquals($century3->getId(),$recs[0]->getId());
		$this->assertEquals($century2->getId(),$recs[1]->getId());

		$rec = TableRecord::FindFirst(array(
			"class" => "TestTable",
			"condition" => array("title=:title1 OR title=:title2","text IS NOT NULL"),
			"bind" => array(":title1" => 'Century', ":title2" => "Another Century"),
			"order" => "title",
		));
		$this->assertEquals($century3->getId(),$rec->getId());
	}

	function test_serialize(){
		$rec = TestTable::CreateNewRecord(array(
			"title" => "Title",
			"price" => 123,
			"an_integer" => 2
		));

		$ser = serialize($rec);
		$this->assertTrue(!preg_match('/[^_]dbmole/',$ser)); // see inobj::__sleep()

		$rec2 = unserialize($ser);
		$this->assertEquals($rec->toArray(),$rec2->toArray());

		$this->assertEquals("postgresql",$rec2->dbmole->getDatabaseType());
		$this->assertEquals("default",$rec2->dbmole->getConfigurationName());

		// --

		$my_rec = MyTestTable::CreateNewRecord(array(
			"title" => "My Title",
			"price" => 456,
			"an_integer" => 3,
		));

		$ser = serialize($my_rec);
		$this->assertTrue(!preg_match('/[^_]dbmole/',$ser)); // see inobj::__sleep()

		$my_rec2 = unserialize($ser);
		$this->assertEquals($my_rec->toArray(),$my_rec2->toArray());

		$this->assertEquals("mysql",$my_rec2->dbmole->getDatabaseType());
		$this->assertEquals("default",$my_rec2->dbmole->getConfigurationName());
	}

	function test_GetSequenceNextval(){
		$id1 = TestTable::GetSequenceNextval();
		$id2 = TestTable::GetSequenceNextval();
		$id3 = TestTable::GetNextId();
		$id4 = TestTable::GetNextId();

		$this->assertTrue(is_numeric($id1));
		$this->assertTrue(is_numeric($id2));
		$this->assertTrue(is_numeric($id3));
		$this->assertTrue(is_numeric($id4));

		$this->assertEquals((int)$id2,$id1+1);
		$this->assertEquals((int)$id3,$id2+1);
		$this->assertEquals((int)$id4,$id3+1);

		// Article has it's own implementation of GetNextId()
		$seq_nextval = Article::GetSequenceNextval();
		$next_id = Article::GetNextId();
		$this->assertEquals($next_id,($seq_nextval+1) * 1000);

		$seq_nextval = Article::GetSequenceNextval();
		$article = Article::CreateNewRecord(array());
		$id = $article->getId();
		$this->assertEquals($id,($seq_nextval+1) * 1000);
	}

	function test_ObjToId(){
		$this->assertEquals(1,TableRecord::ObjToId(1));
		$this->assertEquals("ID",TableRecord::ObjToId("ID"));
		$this->assertEquals(null,TableRecord::ObjToId(null));
		$this->assertEquals(array(),TableRecord::ObjToId(array()));

		// 
		$obj = TestTable::CreateNewRecord(array());
		$obj2 = TestTable::CreateNewRecord(array());

		$this->assertEquals($obj->getId(),TableRecord::ObjToId($obj->getId()));
		$this->assertEquals($obj->getId(),TableRecord::ObjToId($obj));

		$this->assertEquals(array($obj->getId(),$obj2->getId()),TableRecord::ObjToId(array($obj,$obj2)));
		$this->assertEquals(array($obj->getId(),$obj2->getId()),TableRecord::ObjToId(array($obj->getId(),$obj2)));
	}

	function test_setValuesVirtually(){
		$record = TestTable::CreateNewRecord(array(
			"title" => "Blue Savannah",
			"price" => 99.99,
			"an_integer" => -20
		));

		$record->setValuesVirtually(array(
			"title" => "Even Flow",
			"price" => 2.0
		));

		$this->assertEquals(-20,$record->g("an_integer"));
		$this->assertEquals("Even Flow",$record->g("title"));
		$this->assertEquals(2.0,$record->g("price"));

		$rec2 = TestTable::GetInstanceById($record);
		$this->assertEquals("Blue Savannah",$rec2->g("title"));
		$this->assertEquals(99.99,$rec2->g("price"));
		$this->assertEquals(-20,$rec2->g("an_integer"));
	}

	function test__readValues(){
		$record = TestTable::CreateNewRecord(array(
			"title" => "Summer Breeze",
			"price" => 12.34,
			"an_integer" => 1
		));

		$this->dbmole->doQuery("UPDATE test_table SET
				title=:title,
				price=:price,
				an_integer=:an_integer
			WHERE id=:id	
		",array(
			":title" => "Explore, be curious",
			":price" => 56.78,
			":an_integer" => 2,
			":id" => $record
		));

		$record->_readValues("title");
		$this->assertEquals("Explore, be curious",$record->getTitle());
		$this->assertEquals(12.34,$record->getPrice());
		$this->assertEquals(1,$record->getAnInteger());

		$record->_readValues(array("price","an_integer"));
		$this->assertEquals("Explore, be curious",$record->getTitle());
		$this->assertEquals(56.78,$record->getPrice());
		$this->assertEquals(2,$record->getAnInteger());
	}

	function test_virtual_behaviour(){
		$dbmole = $this->dbmole;

		// a fully virtual instance

		$q_cnt = $dbmole->getQueriesExecuted();

		$a = new Article();
		$this->assertEquals(null,$a->getId());
		$this->assertEquals(null,$a->getTitle());
		$this->assertEquals(null,$a->getBody());

		$a->setValuesVirtually(array(
			"title" => "Summer Breeze",
		));
		$this->assertEquals("Summer Breeze",$a->getTitle());

		$array = $a->toArray();
		$keys = array_keys($array);
		sort($keys);
		$this->assertEquals("Summer Breeze",$array["title"]);
		$this->assertEquals("body,created_at,id,image_id,title,updated_at",join(",",$keys));
		$this->assertEquals(null,$a->getBody());

		$a->setValueVirtually("body","La Body");
		$this->assertEquals("La Body",$a->getBody());

		$this->assertEquals($q_cnt,$dbmole->getQueriesExecuted());

		$a = new Article();

		// values set virtually are not saved into the database

		$a = Article::CreateNewRecord(array(
			"title" => "Blood & Fire",
			"created_at" => "2016-07-19 15:17:00",
		));
		
		$q_cnt = $dbmole->getQueriesExecuted();

		$a->setValuesVirtually(array(
			"body" => "No more nights...",
			"title" => "Blood & Fire (Reprise)",
		));

		$this->assertEquals("No more nights...",$a->getBody());
		$this->assertEquals("Blood & Fire (Reprise)",$a->getTitle());
		$this->assertEquals("2016-07-19 15:17:00",$a->getCreatedAt());

		$this->assertEquals($q_cnt,$dbmole->getQueriesExecuted());

		$a2 = Article::GetInstanceById($a->getId());
		$this->assertEquals(null,$a2->getBody());
		$this->assertEquals("Blood & Fire",$a2->getTitle());
		$this->assertEquals("2016-07-19 15:17:00",$a2->getCreatedAt());
	}

	function test__determineSequenceName(){
		$article = new Article();
		$this->assertEquals("seq_articles",$article->getSequenceName());

		$author = new AuthorWithSchema("public.authors");
		$this->assertEquals("public.seq_authors",$author->getSequenceName());

		$tt = new TestTable();
		$this->assertEquals("test_table_id_seq",$tt->getSequenceName());
	}

	function _test_fall($recs){
		$this->assertEquals(1,sizeof($recs));
		$this->assertEquals("Fall",$recs[0]->getTitle());
	}

	function _test_null($recs){
		$this->assertEquals(1,sizeof($recs));
		$this->assertNull($recs[0]->getTitle());
	}

	function _vytvor_testovaci_zaznam($values = array()){
		$values = array_merge(array(
			"title" => "testovaci zaznam",
			"price" => 13.60,
			"an_integer" => 11
		),$values);
		return TestTable::CreateNewRecord($values);
	}
}

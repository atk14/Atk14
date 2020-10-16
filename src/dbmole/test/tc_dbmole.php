<?php
class tc_dbmole extends tc_base{
	function test_uses_sequencies(){
		$this->assertFalse($this->my->usesSequencies());
		//$this->assertTrue($this->ora->usesSequencies());
		$this->assertTrue($this->pg->usesSequencies());
	}

	function test_get_database_type(){
		$this->assertEquals("mysql",$this->my->getDatabaseType());
		$this->assertEquals("postgresql",$this->pg->getDatabaseType());
		//$this->assertEquals("oracle",$this->ora->getDatabaseType());
		$this->assertEquals("unknown",$this->base->getDatabaseType());
	}

	function test_escape_string_4_sql(){
		$this->assertEquals("'o\\'neil \\\"da hacker\\\"'",$this->my->escapeString4Sql("o'neil \"da hacker\""));
		$this->assertEquals("'o''neil \"da hacker\"'",$this->pg->escapeString4Sql("o'neil \"da hacker\""));
	}

	function test_update_returning(){
		$this->pg->insertIntoTable("test_table",array(
			"id" => 99,
			"title" => "Nice",
		));
		$this->assertEquals(99,$this->pg->selectInt("UPDATE test_table SET title='Very nice' WHERE id=99 RETURNING id"));
		$this->assertEquals(null,$this->pg->selectInt("UPDATE test_table SET title='Very nice' WHERE id=99 AND title='Nice' RETURNING id"));
	}

	function test_escape_bool_4_sql(){
		$this->assertEquals('TRUE',$this->pg->escapeBool4Sql(true));
		$this->assertEquals('FALSE',$this->pg->escapeBool4Sql(false));

		$this->assertEquals('TRUE',$this->my->escapeBool4Sql(true));
		$this->assertEquals('FALSE',$this->my->escapeBool4Sql(false));

		//$this->assertEquals("Y",$this->ora->escapeBool4Sql(true));
		//$this->assertEquals("N",$this->ora->escapeBool4Sql(false));
	}

	function test_parse_bool_from_sql(){
		$this->assertEquals(true,$this->pg->parseBoolFromSql("t"));
		$this->assertEquals(false,$this->pg->parseBoolFromSql("f"));

		$this->assertEquals(true,$this->my->parseBoolFromSql("1"));
		$this->assertEquals(false,$this->my->parseBoolFromSql("0"));
	}

	// test for https://github.com/atk14/Atk14/issues/3
	function test_binding(){
		$dbmole = $this->pg;

		$dbmole->selectRow("SELECT title FROM test_table WHERE title IN :title OR title=:title_t",array(":title" => array("test","test2"), ":title_t" => "test3"));
		$this->assertEquals("SELECT title FROM test_table WHERE title IN (:title_0, :title_1) OR title=:title_t",$dbmole->getQuery());
		$this->assertEquals(array(
			":title_0" => "'test'",
			":title_1" => "'test2'",
			":title_t" => "'test3'",
		),$dbmole->getBindAr());
	}

	function test_similar_binding_values(){
		$dbmole = $this->pg;

		$dbmole->doQuery("INSERT INTO test_table (id,title) VALUES(:a1,:a11)",array(
			":a1" => 111,
			":a11" => "Confusing title :a1 :a11 :a111",
		));
		$title = $dbmole->selectSingleValue("SELECT title FROM test_table WHERE id=111");
		$this->assertEquals("Confusing title :a1 :a11 :a111",$title);

		// same test with reversed values in in bind_ar
		$dbmole->doQuery("INSERT INTO test_table (id,title) VALUES(:b1,:b11)",array(
			":b11" => "Confusing title :b1 :b11 :b111",
			":b1" => 222,
		));
		$title = $dbmole->selectSingleValue("SELECT title FROM test_table WHERE id=222");
		$this->assertEquals("Confusing title :b1 :b11 :b111",$title);
	}

	function test_invalid_bind_ar(){
		$dbmoles = $this->_get_moles();

		foreach($this->_get_real_moles() as $dbmole){
			// pro kontrolu, ze nasl. query dopadne dobre..
			$this->assertEquals(true,$dbmole->doQuery("SELECT * FROM test_table WHERE title=:title",array(":title" => "Nice title")));

			$msg = $this->_execute_with_error($dbmole,"doQuery","SELECT * FROM test_table WHERE title=:title",array("123" => "Nice title"));
			$this->assertContains("there is a suspicious key in bind_ar",$msg);

			$this->assertEquals(true,$dbmole->doQuery("SELECT * FROM test_table WHERE title=:title",array(":title" => "Nice title")));

			$msg = $this->_execute_with_error($dbmole,"selectFirstRow","SELECT * FROM test_table WHERE title=:title",array("123" => "Nice title"));
			$this->assertContains("there is a suspicious key in bind_ar",$msg);
			
			$this->assertEquals(null,$dbmole->selectFirstRow("SELECT * FROM test_table WHERE title=:title",array(":title" => "Nice title")));
		}
	}

	function test_common_behaviour(){
		$this->_test_common_behaviour($this->my);
		$this->_test_common_behaviour($this->pg);
		//$this->_test_common_behaviour($this->ora);
	}

	function test_begin_transaction(){
		$this->_test_begin_transaction($this->pg);
		$this->_test_begin_transaction($this->my);

		// oracle is not tested the same way - in oracle 'BEGIN' means nothing by default
		/*
		$this->ora->closeConnection();
		$this->ora->begin();
		$this->assertEquals(false,$this->ora->isConnected());
		$this->ora->commit();
		$this->ora->begin();
		$this->ora->rollback();
		$this->assertEquals(false,$this->ora->isConnected());

		$this->ora->selectFirstRow("SELECT * FROM test_table");
		$this->assertEquals(true,$this->ora->isConnected());
		*/
	}

	function test_serialize(){
		$dbmole = $this->pg;

		$count = $dbmole->selectInt("SELECT COUNT(*) FROM test_table");
		$queries_executed = $dbmole->getQueriesExecuted();

		$this->assertTrue(is_int($count));
		$this->assertTrue($queries_executed>0);

		$ser = serialize($dbmole);

		$dbmole2 = unserialize($ser);
		
		$this->assertTrue($dbmole2->isConnected());

		$count2 = $dbmole2->selectInt("SELECT COUNT(*) FROM test_table");
		$queries_executed2 = $dbmole2->getQueriesExecuted();

		$this->assertTrue(is_int($count2));
		$this->assertTrue($queries_executed2>0);
		$this->assertTrue($queries_executed2>$queries_executed);
	}

	function _test_begin_transaction($dbmole){
		$dbmole->closeConnection();
		$this->assertEquals(false,$dbmole->isConnected());

		$count = $dbmole->getQueriesExecuted();
		$dbmole->begin(array("execute_after_connecting" => false));
		$this->assertEquals(true,$dbmole->isConnected());
		$this->assertEquals($count + 1,$dbmole->getQueriesExecuted());
		$dbmole->selectFirstRow("SELECT * FROM test_table");
		$this->assertEquals($count + 2,$dbmole->getQueriesExecuted());

		// rollback
		$dbmole->closeConnection();
		$count = $dbmole->getQueriesExecuted();
		$dbmole->begin(array("execute_after_connecting" => true));
		$dbmole->rollback();
		$this->assertEquals($count,$dbmole->getQueriesExecuted());
		$this->assertEquals(false,$dbmole->isConnected());
		//
		$dbmole->begin(array("execute_after_connecting" => false));
		$dbmole->rollback();
		$this->assertEquals($count+2,$dbmole->getQueriesExecuted());
		$this->assertEquals(true,$dbmole->isConnected());

		// commit
		$dbmole->closeConnection();
		$count = $dbmole->getQueriesExecuted();
		$dbmole->begin(array("execute_after_connecting" => true));
		$dbmole->commit();
		$this->assertEquals($count,$dbmole->getQueriesExecuted());
		$this->assertEquals(false,$dbmole->isConnected());
		//
		$dbmole->begin(array("execute_after_connecting" => false));
		$dbmole->commit();
		$this->assertEquals($count+2,$dbmole->getQueriesExecuted());
		$this->assertEquals(true,$dbmole->isConnected());

		$dbmole->closeConnection();

		$count = $dbmole->getQueriesExecuted();
		$dbmole->begin(array("execute_after_connecting" => true));
		$this->assertEquals($count,$dbmole->getQueriesExecuted());
		$this->assertEquals(false,$dbmole->isConnected());
		$dbmole->selectFirstRow("SELECT * FROM test_table");
		$this->assertEquals($count + 2,$dbmole->getQueriesExecuted());
	}

	function test_caching(){
		$this->_test_caching($this->pg);
		$this->_test_caching($this->my);
		//$this->_test_caching($this->ora);
	}

	function _test_caching($dbmole){
		$title = "Test Caching on ".get_class($dbmole);
		$dbmole->doQuery("DELETE FROM test_table");
		$dbmole->insertIntoTable("test_table",array(
			"title" => $title,
		));

		$q1 = "SELECT title FROM test_table -- ".uniqid();
		$q2 = "SELECT title FROM test_table -- ".uniqid();
		$q3 = "SELECT title FROM test_table -- ".uniqid();
		$this->assertTrue($q1!=$q2);
		$this->assertTrue($q1!=$q3);
		$this->assertTrue($q2!=$q3);

		$this->assertEquals($title,$dbmole->selectString($q1,array(),array("cache" => 60)));
		$this->assertEquals($title,$dbmole->selectString($q1,array(),array("cache" => true)));
		$this->assertEquals($title,$dbmole->selectString($q3,array(),array("cache" => 60)));

		$dbmole->doQuery("UPDATE test_table SET title=:title",array(":title" => "REWRITTEN"));

		$this->assertEquals($title,$dbmole->selectString($q1,array(),array("cache" => 60)));
		$this->assertEquals($title,$dbmole->selectString($q1,array(),array("cache" => true)));
		$this->assertEquals('REWRITTEN',$dbmole->selectString($q2,array(),array("cache" => 60)));
		$this->assertEquals('REWRITTEN',$dbmole->selectString($q2,array(),array("cache" => true)));

		$this->assertEquals('REWRITTEN',$dbmole->selectString($q3,array(),array("recache" => true)));
		$this->assertEquals('REWRITTEN',$dbmole->selectString($q3,array(),array("cache" => 60)));
		$this->assertEquals('REWRITTEN',$dbmole->selectString($q3,array(),array("cache" => true)));

		$dbmole->doQuery("DELETE FROM test_table");

		$this->assertEquals($title,$dbmole->selectString($q1,array(),array("cache" => 60)));
		$this->assertEquals($title,$dbmole->selectString($q1,array(),array("cache" => true)));
		$this->assertEquals('REWRITTEN',$dbmole->selectString($q2,array(),array("cache" => 60)));
		$this->assertEquals('REWRITTEN',$dbmole->selectString($q2,array(),array("cache" => true)));
		$this->assertEquals('REWRITTEN',$dbmole->selectString($q3,array(),array("cache" => 60)));

		$this->assertEquals($title,$dbmole->selectSingleValue($q1,array(),array("cache" => 60)));
		$this->assertEquals($title,$dbmole->selectSingleValue($q1,array(),array("cache" => true)));
		$this->assertEquals('REWRITTEN',$dbmole->selectSingleValue($q2,array(),array("cache" => 60)));
		$this->assertEquals('REWRITTEN',$dbmole->selectSingleValue($q2,array(),array("cache" => true)));
		$this->assertEquals('REWRITTEN',$dbmole->selectSingleValue($q3,array(),array("cache" => 60)));

		$this->assertEquals(null,$dbmole->selectString($q1));
		$this->assertEquals(null,$dbmole->selectString($q2));
		$this->assertEquals(null,$dbmole->selectString($q3));
	}

	function test_pgmole(){
		$dbmole = &$this->pg;
		$this->_test_select_sequence($dbmole);
	}

	/*
	function test_oraclemole(){
		$dbmole = &$this->ora;

		$this->_test_select_sequence($dbmole);

		$dbmole->insertIntoTable("test_table",array(
			"id" => "-123",
			"binary_data" => $this->_binary_data(),
		),array(
			"blobs" => array("binary_data")
		));

		$row = $dbmole->selectFirstRow("SELECT * FROM test_table WHERE id=:id",array(":id" => -123));
		$this->assertEquals(256,strlen($row["binary_data"]));
		$this->assertEquals($this->_binary_data(),$row["binary_data"]);

		$dbmole->insertIntoTable("test_table",array(
			"id" => "-124",
			"binary_data" => $this->_binary_data(),
			"binary_data2" => $this->_binary_data(1000),
			"text" => $this->_lorem_ipsum(),
		),array(
			"blobs" => array("binary_data","binary_data2"),
			"clobs" => array("text")
		));
		$row = $dbmole->selectFirstRow("SELECT * FROM test_table WHERE id=:id",array(":id" => -124));
		$this->assertEquals(256,strlen($row["binary_data"]));
		$this->assertEquals($this->_binary_data(),$row["binary_data"]);
		$this->assertEquals(1000*256,strlen($row["binary_data2"]));
		$this->assertEquals($this->_binary_data(1000),$row["binary_data2"]);
		$this->assertEquals($this->_lorem_ipsum(),$row["text"]);
	}*/

	function test_error_handlers(){
		$dbmole = PgMole::GetInstance();
		$dbmole_archive = PgMole::GetInstance("archive");
		$dbmole_session = PgMole::GetInstance("session");

		$this->assertEquals("dbmole_error_handler",$dbmole->getErrorHandler());
		$this->assertEquals("dbmole_error_handler",$dbmole_archive->getErrorHandler());
		$this->assertEquals("dbmole_error_handler",$dbmole_session->getErrorHandler());

		$dbmole_session->setErrorHandler("session_error_handler");

		$this->assertEquals("dbmole_error_handler",$dbmole->getErrorHandler());
		$this->assertEquals("dbmole_error_handler",$dbmole_archive->getErrorHandler());
		$this->assertEquals("session_error_handler",$dbmole_session->getErrorHandler());

		$dm = PgMole::GetInstance();
		$this->assertEquals("session_error_handler",$dbmole_session->getErrorHandler());

		$dbmole_archive->setErrorHandler(function($dbmole){ });
		$this->assertEquals(true,is_a($dbmole_archive->getErrorHandler(),"Closure"));

		$prev = DbMole::RegisterErrorHandler(function($dbmole_archive){ });
		$this->assertEquals("dbmole_error_handler",$prev);
		
		$this->assertEquals(true,is_a($dbmole_archive->getErrorHandler(),"Closure"));
		$this->assertEquals(true,is_a($dbmole->getErrorHandler(),"Closure"));
		$this->assertEquals("session_error_handler",$dbmole_session->getErrorHandler());

		$prev = DbMole::RegisterErrorHandler($prev);
		$this->assertEquals(true,is_a($prev,"Closure"));
		$this->assertEquals("dbmole_error_handler",$dbmole->getErrorHandler());
	}

	function test_sendErrorReportToEmail_limit_sending_rate(){
		$dbmole = PgMole::GetInstance();
		$sending_lock_file = Files::GetTempDir()."/testing_dbmole_email_sent_".uniqid();

		$this->assertEquals(false,file_exists($sending_lock_file));

		$ret = $dbmole->sendErrorReportToEmail("john@doe.com",array(
			"sending_lock_file" => $sending_lock_file
		));
		$this->assertTrue(is_array($ret));
		$this->assertEquals(true,file_exists($sending_lock_file));

		$ret = $dbmole->sendErrorReportToEmail("john@doe.com",array(
			"sending_lock_file" => $sending_lock_file
		));
		$this->assertEquals(null,$ret);

		$ret = $dbmole->sendErrorReportToEmail("john@doe.com",array(
			"sending_lock_file" => $sending_lock_file,
			"limit_sending_rate" => 0,
		));
		$this->assertTrue(is_array($ret));

		unlink($sending_lock_file);

		$ret = $dbmole->sendErrorReportToEmail("john@doe.com",array(
			"sending_lock_file" => $sending_lock_file
		));
		$this->assertTrue(is_array($ret));

		unlink($sending_lock_file);
	}

	function test_getDatabaseVersion(){
		foreach(array($this->pg,$this->my) as $dbmole){

			// Server version

			$server_version_str = $dbmole->getDatabaseServerVersion();
			$this->assertTrue(is_string($server_version_str));
			$this->assertTrue(strlen($server_version_str)>0);
			$this->assertTrue(!!preg_match('/^\d+\.\d+/',$server_version_str));

			$server_version_ary = $dbmole->getDatabaseServerVersion(array("as_array" => true));
			$this->assertTrue(is_array($server_version_ary));
			$this->assertTrue(is_int($server_version_ary["major"]));
			$this->assertTrue($server_version_ary["major"]>0);
			$this->assertTrue(is_int($server_version_ary["minor"]));
			$this->assertTrue(is_int($server_version_ary["patch"]));
			$this->assertEquals($server_version_str,"$server_version_ary[major].$server_version_ary[minor].$server_version_ary[patch]");

			$server_version_float = $dbmole->getDatabaseServerVersion(array("as_float" => true));
			$this->assertTrue(is_float($server_version_float));
			$this->assertEquals((float)(sprintf("%s.%02d%03d",$server_version_ary["major"],$server_version_ary["minor"],$server_version_ary["patch"])),$server_version_float);

			$this->assertEquals($server_version_ary,$dbmole->getDatabaseServerVersion("as_array"));
			$this->assertEquals($server_version_float,$dbmole->getDatabaseServerVersion("as_float"));

			// Client version

			$client_version_str = $dbmole->getDatabaseClientVersion();
			$this->assertTrue(is_string($client_version_str));
			$this->assertTrue(strlen($client_version_str)>0);
			$this->assertTrue(!!preg_match('/^\d+\.\d+/',$client_version_str));

			$client_version_ary = $dbmole->getDatabaseClientVersion(array("as_array" => true));
			$this->assertTrue(is_array($client_version_ary));
			$this->assertTrue(is_int($client_version_ary["major"]));
			$this->assertTrue($client_version_ary["major"]>0);
			$this->assertTrue(is_int($client_version_ary["minor"]));
			$this->assertTrue(is_int($client_version_ary["patch"]));
			$this->assertEquals($client_version_str,"$client_version_ary[major].$client_version_ary[minor].$client_version_ary[patch]");

			$client_version_float = $dbmole->getDatabaseClientVersion(array("as_float" => true));
			$this->assertTrue(is_float($client_version_float));
			$this->assertEquals((float)(sprintf("%s.%02d%03d",$client_version_ary["major"],$client_version_ary["minor"],$client_version_ary["patch"])),$client_version_float);

			$this->assertEquals($client_version_ary,$dbmole->getDatabaseClientVersion("as_array"));
			$this->assertEquals($client_version_float,$dbmole->getDatabaseClientVersion("as_float"));
		}
	}

	function test__parseVersion(){
		$dbmole = new ProxyDbMole();
		$this->assertEquals(array("major" => 9, "minor" => 6, "patch" => 16),$dbmole->parseVersion("9.6.16",array("as_array" => true)));
		$this->assertEquals(array("major" => 9, "minor" => 6, "patch" => 0),$dbmole->parseVersion("9.6",array("as_array" => true)));

		$this->assertEquals(9.06016,$dbmole->parseVersion("9.6.16",array("as_float" => true)));
		$this->assertEquals(9.616,$dbmole->parseVersion("9.6.16",array("as_float" => true,"minor_number_divider" => 10, "patch_number_divider" => 1000)));
	}

	function _test_common_behaviour(&$dbmole){
		$this->assertTrue($dbmole->doQuery("DELETE FROM test_table"));

		$this->_test_table_count($dbmole,0);

		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"title" => "O'neil & daughter",
			"an_integer" => 11,
			"price" => 15.8,
			"text" => "\"O'neil has just 1 daughter\"",
			"create_date" => "2009-12-01",
			"flag" => false
		)));

		$this->_test_table_count($dbmole,1);

		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"title" => "O'neil & sons",
			"an_integer" => 22,
			"price" => 33.3,
			"text" => "\"O'neil has 2 sons\"",
			"create_date" => "2009-12-31",
			"flag" => true
		)));

		$this->_test_table_count($dbmole,2);

		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"title" => "O'neil & daughter",
			"an_integer" => 33,
			"price" => 15.8,
			"text" => "\"O'neil has just 1 daughter\"",
			"create_date" => "2009-12-31",
			"flag" => NULL,
		)));

		$this->_test_table_count($dbmole,3);
		
		$rows = $dbmole->selectRows("SELECT title,an_integer,price,text,create_date,flag FROM test_table ORDER BY an_integer");
		$rows[0]["create_date"] = preg_replace("/ .*$/","",$rows[0]["create_date"]); // 2009-12-31 00:00:00 -> 2009-12-31
		$rows[1]["create_date"] = preg_replace("/ .*$/","",$rows[1]["create_date"]);
		$rows[2]["create_date"] = preg_replace("/ .*$/","",$rows[2]["create_date"]);
		settype($rows[0]["price"],"float");
		settype($rows[1]["price"],"float");
		settype($rows[2]["price"],"float");

		$rows[0]['flag']=$dbmole->parseBoolFromSql($rows[0]['flag']);
		$rows[1]['flag']=$dbmole->parseBoolFromSql($rows[1]['flag']);
		$rows[2]['flag']=$dbmole->parseBoolFromSql($rows[2]['flag']);

		$this->assertEquals(array(
			array (
				'title' => "O'neil & daughter",
				'an_integer' => '11',
				'price' => 15.8,
				'text' => '"O\'neil has just 1 daughter"',
				'create_date' => '2009-12-01',
				'flag' => false
			),
			array (
				'title' => "O'neil & sons",
				'an_integer' => '22',
				'price' => 33.3,
				'text' => '"O\'neil has 2 sons"',
				'create_date' => '2009-12-31',
				'flag' => true,
			),
			array (
				'title' => "O'neil & daughter",
				'an_integer' => '33',
				'price' => 15.8,
				'text' => '"O\'neil has just 1 daughter"',
				'create_date' => '2009-12-31',
				'flag' => null,
			),
		),$rows);

		$row = $dbmole->selectFirstRow("SELECT title,an_integer,price,text,create_date FROM test_table ORDER BY an_integer DESC");
		$row["create_date"] = preg_replace("/ .*$/","",$row["create_date"]);
		$row["create_date"] = preg_replace("/ .*$/","",$row["create_date"]);
		settype($row["price"],"float");
		settype($row["price"],"float");
		$this->assertEquals(
			array (
				'title' => "O'neil & daughter",
				'an_integer' => '33',
				'price' => 15.8,
				'text' => '"O\'neil has just 1 daughter"',
				'create_date' => '2009-12-31',
			),
		$row);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array("11","22","33"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1));
		$this->assertEquals(array("11"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1, "offset" => 0));
		$this->assertEquals(array("11"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1, "offset" => 1));
		$this->assertEquals(array("22"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1, "offset" => 2));
		$this->assertEquals(array("33"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 1, "offset" => 3));
		$this->assertEquals(array(),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table WHERE an_integer=-an_integer");
		$this->assertEquals(array(),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 3, "offset" => 0));
		$this->assertEquals(array("11","22","33"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 0, "offset" => 0));
		$this->assertEquals(array(),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 3, "offset" => -1));
		$this->assertEquals(array("11","22"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 3, "offset" => -2));
		$this->assertEquals(array("11"),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => 3, "offset" => -3));
		$this->assertEquals(array(),$ar);

		$ar = $dbmole->selectIntoArray("SELECT an_integer FROM test_table ORDER BY an_integer",array(),array("limit" => -10, "offset" => -10));
		$this->assertEquals(array(),$ar);

		$ar = $dbmole->selectIntoAssociativeArray("SELECT an_integer,an_integer+1 FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array("11" => "12","22" => "23","33" => "34"),$ar);

		$ar = $dbmole->selectIntoAssociativeArray("SELECT an_integer,an_integer+1 as f1, an_integer+2 as f2 FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array(
			"11" => array("f1" => "12", "f2" => "13"),
			"22" => array("f1" => "23", "f2" => "24"),
			"33" => array("f1" => "34", "f2" => "35")
		),$ar);

		$ar = $dbmole->selectIntoAssociativeArray("SELECT an_integer,an_integer+1 as f1, an_integer+2 as f2, null as f3 FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array(
			"11" => array("f1" => "12", "f2" => "13", "f3" => null),
			"22" => array("f1" => "23", "f2" => "24", "f3" => null),
			"33" => array("f1" => "34", "f2" => "35", "f3" => null)
		),$ar);

		// je docela dobry nesmysl vybirat do asociativniho pole jeden sloupec
		$ar = $dbmole->selectIntoAssociativeArray("SELECT an_integer FROM test_table ORDER BY an_integer",array());
		$this->assertEquals(array(
			"11" => array(),
			"22" => array(),
			"33" => array(),
		),$ar);

		$this->assertTrue($dbmole->doQuery("UPDATE test_table SET an_integer=44 WHERE an_integer=22"));
		$this->assertEquals(1,$dbmole->getAffectedRows(),"calling getAffectedRows() on ".$dbmole->getDatabaseType());

		$this->assertTrue($dbmole->doQuery("UPDATE test_table SET an_integer=44 WHERE an_integer=-1234"));
		$this->assertEquals(0,$dbmole->getAffectedRows(),"calling getAffectedRows() on ".$dbmole->getDatabaseType());

		$this->assertEquals("44",$dbmole->selectSingleValue("SELECT MAX(an_integer) FROM test_table"));

		// do_not_escape
		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"id" => -333,
			"an_integer" => "22-12",
			"text" => "testing"
		),array(
			"do_not_escape" => "an_integer"
		)));
		$row = $dbmole->selectFirstRow("SELECT an_integer,text FROM test_table WHERE id=-333");
		$this->assertEquals(array("an_integer" => "10","text" => "testing"),$row);

		$this->assertTrue($dbmole->insertIntoTable("test_table",array(
			"id" => -444,
			"an_integer" => "23-12",
			"text" => "testing 2"
		),array(
			"do_not_escape" => array("an_integer")
		)));
		$row = $dbmole->selectFirstRow("SELECT an_integer,text FROM test_table WHERE id=-444");
		$this->assertEquals(array("an_integer" => "11","text" => "testing 2"),$row);


		$ints = $dbmole->selectIntoArray("SELECT an_integer FROM test_table WHERE an_integer IN :ints ORDER BY an_integer",array(
			":ints" => array(10,33),
		));

		$this->assertEquals(array("10","33"),$ints);
	}

	function _test_select_sequence(&$dbmole){
		$s1 = (int)$dbmole->selectSequenceNextval("test_table_id_seq");
		$s2 = (int)$dbmole->selectSequenceNextval("test_table_id_seq");
		$s3 = (int)$dbmole->selectSequenceCurrval("test_table_id_seq");

		$this->assertTrue($s2>$s1);
		$this->assertTrue($s3==$s2);
	}

	function _test_table_count(&$dbmole,$expected_count){
		$q = "SELECT COUNT(*) FROM test_table";

		$this->_test_string($dbmole->selectSingleValue($q),"$expected_count");

		$this->_test_integer($dbmole->selectSingleValue($q,"integer"),(int)$expected_count);
		$this->_test_integer($dbmole->selectSingleValue($q,array(),"integer"),(int)$expected_count);
		$this->_test_integer($dbmole->selectSingleValue($q,array(),array("type" => "integer")),(int)$expected_count);
		$this->_test_integer($dbmole->selectInt($q),(int)$expected_count);

		$this->_test_float($dbmole->selectSingleValue($q,"float"),(float)$expected_count);
		$this->_test_float($dbmole->selectSingleValue($q,array(),"float"),(float)$expected_count);
		$this->_test_float($dbmole->selectSingleValue($q,array(),array("type" => "float")),(float)$expected_count);
		$this->_test_float($dbmole->selectFloat($q),(float)$expected_count);
	}

	function _test_integer($i,$expected_val,$msg = ""){
		$this->assertTrue(is_integer($i),$msg);
		$this->assertEquals($expected_val,$i);
	}

	function _test_float($f,$expected_val,$msg = ""){
		$this->assertTrue(is_float($f),$msg);
		$this->assertEquals($expected_val,$f);
	}

	function _test_string($i,$expected_val,$msg = ""){
		$this->assertTrue(is_string($i),$msg);
		$this->assertEquals($expected_val,$i);
	}

	function _binary_data($repeated = 1){
		$out = array();

		for($i=0;$i<=255;$i++){
			$out[] = chr($i);
		}
		return str_repeat(join("",$out),$repeated);
	}

	function _lorem_ipsum(){
		return 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Typi non habent claritatem insitam; est usus legentis in iis qui facit eorum claritatem. Investigationes demonstraverunt lectores legere me lius quod ii legunt saepius. Claritas est etiam processus dynamicus, qui sequitur mutationem consuetudium lectorum. Mirum est notare quam littera gothica, quam nunc putamus parum claram, anteposuerit litterarum formas humanitatis per seacula quarta decima et quinta decima. Eodem modo typi, qui nunc nobis videntur parum clari, fiant sollemnes in futurum.';
	}
}

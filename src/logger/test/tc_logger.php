<?php
class TcLogger extends TcBase{

	function test(){
		global $LOGGER_CONFIGURATION; // see initialize.php

		$this->_test_log_file_creation(new Logger("robot"),"default.log");

		//

		$this->_test_log_file_creation(new Logger("cache_remover"),"cache_remover.log");

		//
		 
		$this->_test_log_file_creation(new Logger("import_data"),"import.log");

		//
		$this->_test_log_file_creation(new Logger("special_robot"),"default.log");
		$this->_test_log_file_creation(new Logger("special_robot",array("default_log_file" => __DIR__."/log/another.log")),"another.log");

		$LOGGER_CONFIGURATION["special_robot"] = array(
			"log_file" => __DIR__."/log/special.log",
		);

		$this->_test_log_file_creation(new Logger("special_robot"),"special.log");
		$this->_test_log_file_creation(new Logger("special_robot",array("default_log_file" => __DIR__."/log/another.log")),"special.log");

		//

		$log_file = __DIR__."/log/default.log";

		$this->assertFalse(file_exists($log_file));

		$logger = new Logger("robot",array(
			"log_to_stdout" => true,
		));
		$logger->info("TEST");
		ob_start();
		$logger->flushAll();
		$content = ob_get_clean();
		$this->assertFalse(file_exists($log_file));
		$this->assertContains("TEST",$content);

		$logger = new Logger("robot",array(
			"log_to_stdout" => true,
			"log_to_file" => true,
		));
		$logger->info("TST2");
		ob_start();
		$logger->flushAll();
		$content = ob_get_clean();
		$this->assertTrue(file_exists($log_file));
		$this->assertContains("TST2",$content);

		unlink($log_file);

		$logger = new Logger("robot",array(
			"log_to_stdout" => false,
			"log_to_file" => true,
		));
		$logger->info("TST3");
		ob_start();
		$logger->flushAll();
		$content = ob_get_clean();
		$this->assertTrue(file_exists($log_file));
		$this->assertEquals("",$content);

		unlink($log_file);
	}

	function test_prefixes(){
		$logger = new Logger();
		$this->assertEquals("",$logger->get_prefix());

		$logger->set_prefix("test");
		$this->assertEquals("test",$logger->get_prefix());

		$logger = new Logger("import");
		$this->assertEquals("import",$logger->get_prefix());

		$logger = new Logger(array(
			"prefix" => "robot"
		));
		$this->assertEquals("robot",$logger->get_prefix());
	}

	function test_levels(){
		$logger = new Logger();

		$this->assertEquals("error",$logger->level_to_str("4"));
		$this->assertEquals(4,$logger->level_to_int("error"));

		$this->assertEquals("#d00b00",$logger->level_to_color(4));
		$this->assertEquals("#d00b00",$logger->level_to_color("4"));
		$this->assertEquals("#d00b00",$logger->level_to_color("error"));

		$this->assertEquals("#c66905",$logger->level_to_color("warn"));
		$this->assertEquals("#c66905",$logger->level_to_color("warn++"));
		$this->assertEquals("#c66905",$logger->level_to_color("2"));
		$this->assertEquals("#c66905",$logger->level_to_color("3"));
	}

	function test__send_email_notification(){
		$logger = new Logger(array("default_notify_email" => "samantha@doe.com"));

		$logger->flush();
		$this->assertEquals(null,$logger->_send_email_notification());

		$logger->error("Something went wrong");
		$logger->flush();

		$mail_ar = $logger->_send_email_notification();
		$this->assertTrue(is_array($mail_ar));
		$this->assertEquals("samantha@doe.com",$mail_ar["to"]);

		// Logger without notification email address
		$logger = new Logger(array("default_notify_email" => ""));

		$logger->error("Something went wrong");
		$logger->flush();

		$this->assertEquals(null,$logger->_send_email_notification());
	}

	function test_get_notify_email(){
		$logger = new Logger("test",array("default_notify_email" => "john@doe.com"));
		$this->assertEquals("john@doe.com",$logger->get_notify_email());

		$logger = new Logger("import_articles",array("default_notify_email" => "john@doe.com"));
		$this->assertEquals("import.notification@doe.com",$logger->get_notify_email()); // see $LOGGER_CONFIGURATION in initialize.php
	}

	function test_log_to_buffer(){
		$logger = new Logger("test",array("log_to_file" => false));
		$this->assertNull($logger->buffer);

		$logger = new Logger("test",array("log_to_file" => false, "log_to_buffer" => false));
		$this->assertNull($logger->buffer);

		$logger = new Logger("test",array("log_to_file" => false, "log_to_buffer" => true));
		$this->assertNotNull($logger->buffer);
		$this->assertTrue(is_a($logger->buffer,"StringBuffer"));

		$logger->info("Writing to buffer");

		$this->assertTrue($logger->buffer->getLength() === 0);

		$logger->flush();

		$this->assertFalse($logger->buffer->getLength() === 0);
		$this->assertTrue(!!preg_match('/test\[\d+\]: Writing to buffer/',$logger->buffer)); // e.g. "2022-06-26 17:18:27 test[50604]: Writing to buffer"
	}

	function _test_log_file_creation($logger,$log_name){
		$this->assertFalse(file_exists($f = __DIR__."/log/$log_name"));

		$logger->start();
		$logger->info("doing some normal job");
		$logger->error("Damn, this is bad!");
		$logger->stop();
		$logger->flushAll();

		$this->assertTrue(file_exists($f));

		$content = Files::GetFileContent($f);

		$this->assertContains("START",$content);
		$this->assertContains("doing some normal job",$content);
		$this->assertContains("ERROR: Damn, this is bad!",$content);
		$this->assertContains("STOP",$content);

		unlink($f);
	}
}

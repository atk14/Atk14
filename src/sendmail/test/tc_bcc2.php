<?php
define("BCC_EMAIL","bcc_email@default.com");

class tc_bcc2 extends tc_base {

	function test(){
		$ar = sendmail(array(
			"to" => "me@mydomain.com",
			"from" => "test@file",
			"subject" => "Hello from unit test",
			"body" => "Hi there"
		));

		$this->assertEquals("bcc_email@default.com",$ar["bcc"]);

		$ar = sendmail(array(
			"bcc" => "admin@localhost"
		));
		$this->assertEquals("admin@localhost, bcc_email@default.com",$ar["bcc"]);

		$ar = sendmail(array(
			"bcc" => array("admin@localhost","root@localhost")
		));
		$this->assertEquals("admin@localhost, root@localhost, bcc_email@default.com",$ar["bcc"]);
	}
}

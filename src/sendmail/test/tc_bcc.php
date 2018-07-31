<?php
define("SENDMAIL_BCC_TO","big.brother@somewhere.com");

class tc_bcc extends tc_base {

	function test(){
		$ar = sendmail(array(
			"to" => "me@mydomain.com",
			"from" => "test@file",
			"subject" => "Hello from unit test",
			"body" => "Hi there"
		));

		$this->assertEquals("big.brother@somewhere.com",$ar["bcc"]);

		$ar = sendmail(array(
			"bcc" => "admin@localhost"
		));
		$this->assertEquals("admin@localhost, big.brother@somewhere.com",$ar["bcc"]);

		$ar = sendmail(array(
			"bcc" => array("admin@localhost","root@localhost")
		));
		$this->assertEquals("admin@localhost, root@localhost, big.brother@somewhere.com",$ar["bcc"]);
	}
}

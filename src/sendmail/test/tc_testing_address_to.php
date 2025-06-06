<?php
define("SENDMAIL_USE_TESTING_ADDRESS_TO","test@listonos.cz");
class TcTestingAddressTo extends TcBase {

	function test(){
		$ar = sendmail([
			"to" => "John Doe <john@doe.com>",
			"cc" => "Boss <boss@globe.com>",
			"bcc" => "big@brother.org",
			"subject" => "Subject",
			"body" => "Body"
		]);

		$this->assertEquals("test@listonos.cz",$ar["to"]);
		$this->assertEquals("",$ar["cc"]);
		$this->assertEquals("",$ar["bcc"]);
		$this->assertStringContains("X-Original-To: John Doe <john@doe.com>",$ar["headers"]);
		$this->assertStringContains("X-Original-Cc: Boss <boss@globe.com>",$ar["headers"]);
		$this->assertStringContains("X-Original-Bcc: big@brother.org",$ar["headers"]);
	}
}

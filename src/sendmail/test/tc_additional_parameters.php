<?php
define("SENDMAIL_MAIL_ADDITIONAL_PARAMETERS","-fbounce@example.com");

class tc_additional_parameters extends tc_base {

	function test(){
//var_dump($GLOBALS); exit;
		$ar = sendmail("joh@doe.com","Subject","Body");
		$this->assertEquals("-fbounce@example.com",$ar["additional_parameters"]); // default

		$ar = sendmail("joh@doe.com","Subject","Body", "", "");
		$this->assertEquals("",$ar["additional_parameters"]); // no additional_parameters reqested

		$ar = sendmail("joh@doe.com","Subject","Body", "", null);
		$this->assertEquals("-fbounce@example.com",$ar["additional_parameters"]); // default

		$ar = sendmail("joh@doe.com","Subject","Body", "", "-fjohn@doe.com");
		$this->assertEquals("-fjohn@doe.com",$ar["additional_parameters"]); // default
	}
}

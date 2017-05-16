<?php
class TcModel extends TcBase {

	// There are no TableRecord models.
	// Here are few tests to check that our mock models are working properly.
	function test(){
		$john = User::CreateNewRecord(array(
			"login" => "john.doe",
			"name" => "John Doe"
		));
		$samantha = User::CreateNewRecord(array(
			"login" => "samantha.doe",
			"name" => "Samantha Doe",
		));

		$this->assertEquals(1,$john->getId());
		$this->assertEquals(2,$samantha->getId());

		$this->assertEquals("john.doe",$john["login"]);
		$this->assertEquals("john.doe",$john->getLogin());

		$this->assertEquals("samantha.doe",$samantha["login"]);
		$this->assertEquals("samantha.doe",$samantha->getLogin());
	}
}


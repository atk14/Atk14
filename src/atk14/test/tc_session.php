<?php
class TcSession extends TcBase{
	function setUp(){
		global $_COOKIE,$HTTP_REQUEST;
		$HTTP_REQUEST->setRemoteAddr("127.0.0.1");
		$_COOKIE[SESSION_STORER_COOKIE_NAME_CHECK] = "1";
		parent::setUp();
	}

	function test(){
		$session = Atk14Session::GetInstance();

		$this->assertEquals(null,$session->getValue("user_id"));
		$session->setValue("user_id",123);
		$this->assertEquals(123,$session->getValue("user_id"));

		$token = $session->getSecretToken();
		$this->assertTrue(strlen($token)>0);

		$new_token = $session->changeSecretToken();
		$this->assertTrue($new_token!=$token);

		// another change should not happened
		$new_token_2 = $session->changeSecretToken();
		$this->assertTrue($new_token_2==$new_token);
	}

	function test_toArray(){
		$session = Atk14Session::GetInstance("test_toArray");
		$session->clear();

		$this->assertEquals(array(),$session->toArray());

		$session->setValue("name","John Doe");
		$session->setValue("vegetables",array("Cauliflower","Cucumber"));

		$this->assertEquals(array(
			"name" => "John Doe",
			"vegetables" => array("Cauliflower","Cucumber")
		),$session->toArray());
	}
}

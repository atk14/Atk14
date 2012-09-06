<?php
class TcSession extends TcBase{
	function test(){
		global $_COOKIE;

		$_COOKIE[SESSION_STORER_COOKIE_NAME_CHECK] = "1";

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
}

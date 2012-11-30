<?php
class TcSessionStorer extends TcBase{
	function test__setCheckCookieWhenNeeded(){
		global $_COOKIE,$HTTP_REQUEST;

		$s = new SessionStorer();
		$sent_cookies = $s->getSentCookies();
		$this->assertEquals(1,sizeof($sent_cookies));
		$this->assertEquals(SESSION_STORER_COOKIE_NAME_CHECK,$sent_cookies[0][0]);
		$this->assertEquals("1",$sent_cookies[0][1]);

		$_COOKIE[SESSION_STORER_COOKIE_NAME_CHECK] = "1";
		$s = new SessionStorer();
		$sent_cookies = $s->getSentCookies();
		$this->assertEquals(0,sizeof($sent_cookies));
	}

	function test_cookiesEnabled(){
		global $_COOKIE;

		$s = new SessionStorer();

		$_COOKIE = array();
		$this->assertFalse($s->cookiesEnabled());

		$_COOKIE = array("key" => "val");
		$this->assertTrue($s->cookiesEnabled());
	}

	function test__setSessionCookie(){
		$s = new SessionStorer();
		$this->assertEquals(null,$s->getSecretToken());
		$this->assertEquals(1,sizeof($s->getSentCookies())); // testing cookies

		$s->_createNewDatabaseSession();
		$this->assertEquals(true,strlen($token = $s->getSecretToken())>0);
		$this->assertEquals(2,sizeof($s->getSentCookies())); // testing cookies
	}

	function test__clearDataCookies(){
		global $_COOKIE;

		$s = new SessionStorer();

		$this->assertEquals(0,$s->_clearDataCookies());

		// these two are not going to be deleted
		$_COOKIE["check"] = "1";
		$_COOKIE["session"] = "123.aRightlyLookingToken";

		$_COOKIE["session0"] = "fake_data";
		$_COOKIE["session3"] = "fake_data";
		$_COOKIE["session99"] = "fake_data";

		$this->assertEquals(3,$s->_clearDataCookies());

		$_COOKIE["session"] = array(
			0 => "another_fake",
			33 => "yet_another_fake"
		);

		$this->assertEquals(5,$s->_clearDataCookies());
	}

	// TODO: we realy need more tests!
}

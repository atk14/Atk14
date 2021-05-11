<?php
class TcSessionStorer extends TcBase{
	function test_different_sections_on_same_session(){
		global $_COOKIE;
		$_COOKIE = array();

		$s = new SessionStorer(); // "default"
		$s->_createNewDatabaseSession();
		$this->_add_cookies($s->getSentCookies(),$_COOKIE);
		$this->assertEquals(true,!!strlen($s->getSecretToken()));

		$s_admin = new SessionStorer("admin");
		$this->assertEquals($s->getSecretToken(),$s_admin->getSecretToken()); // same token
		$this->assertTrue("session"==$s->getCookieName() && "session"==$s_admin->getCookieName()); // same cookie name

		$s->writeValue("logged_user","jack.ordinary");
		$s->writeValue("credit",123);
		$s_admin->writeValue("logged_user","john.admin");

		$sess = new SessionStorer();
		$this->assertEquals("jack.ordinary",$sess->readValue("logged_user"));
		$this->assertEquals(123,$sess->readValue("credit"));

		$sess = new SessionStorer("admin");
		$this->assertEquals("john.admin",$sess->readValue("logged_user"));
		$this->assertEquals(null,$sess->readValue("credit"));

		// well, let's have another session with another two sections 
		
		$secured = new SessionStorer(array("session_name" => "secured"));
		$secured->_createNewDatabaseSession();
		$this->_add_cookies($secured->getSentCookies(),$_COOKIE);
		$this->assertEquals(true,!!strlen($secured->getSecretToken()));
		$this->assertTrue($s->getSecretToken()!=$secured->getSecretToken());

		$secured_admin = new SessionStorer("admin",array("session_name" => "secured"));
		$this->assertEquals($secured->getSecretToken(),$secured_admin->getSecretToken());
		$this->assertTrue("secured"==$secured->getCookieName() && "secured"==$secured_admin->getCookieName());

		$this->assertEquals(null,$secured->readValue("logged_user"));
		$this->assertEquals(null,$secured->readValue("credit"));
		$this->assertEquals(null,$secured_admin->readValue("logged_user"));
		$this->assertEquals(null,$secured_admin->readValue("credit"));

		$secured->writeValue("logged_user","amanda.careful");
		$secured->writeValue("credit",432);

		$secured_admin->writeValue("logged_user","samantha.armored");

		$sess = new SessionStorer(array("session_name" => "secured"));
		$this->assertEquals("amanda.careful",$sess->readValue("logged_user"));
		$this->assertEquals(432,$sess->readValue("credit"));

		$sess = new SessionStorer("admin",array("session_name" => "secured"));
		$this->assertEquals("samantha.armored",$sess->readValue("logged_user"));
		$this->assertEquals(null,$sess->readValue("credit"));
	}

	function test__setCheckCookieWhenNeeded(){
		global $_COOKIE,$HTTP_REQUEST;

		$s = new SessionStorer();
		$sent_cookies = $s->getSentCookies();
		$this->assertEquals(1,sizeof($sent_cookies));
		$this->assertEquals(SESSION_STORER_COOKIE_NAME_CHECK,$sent_cookies[0][0]);
		$this->assertEquals(CURRENT_TIME,$sent_cookies[0][1]);

		$_COOKIE[SESSION_STORER_COOKIE_NAME_CHECK] = CURRENT_TIME - 1000;
		$s = new SessionStorer();
		$sent_cookies = $s->getSentCookies();
		$this->assertEquals(0,sizeof($sent_cookies));

		$_COOKIE[SESSION_STORER_COOKIE_NAME_CHECK] = CURRENT_TIME - 60*60*24*365*3;
		$s = new SessionStorer();
		$sent_cookies = $s->getSentCookies();
		$this->assertEquals(1,sizeof($sent_cookies));
		$this->assertEquals(SESSION_STORER_COOKIE_NAME_CHECK,$sent_cookies[0][0]);
		$this->assertEquals(CURRENT_TIME,$sent_cookies[0][1]);
		$this->assertEquals(CURRENT_TIME + 60*60*24*365*5,$sent_cookies[0][2]); // 5 years
	}

	function test_setting_ssl_cookie(){
		global $_COOKIE,$_SERVER;

		$_SERVER["HTTPS"] = "on";
		$_COOKIE = array();
		$s = new SessionStorer(array("ssl_only" => true));
		$s->_createNewDatabaseSession();
		$this->assertEquals(2,sizeof($s->getSentCookies())); // on https check and session cookies are set

		unset($_SERVER["HTTPS"]);
		$_COOKIE = array();
		$s = new SessionStorer(array("ssl_only" => true));
		$s->_createNewDatabaseSession();
		$this->assertEquals(1,sizeof($s->getSentCookies())); // on http only check cookie is set

		$_COOKIE = array();
		$s = new SessionStorer();
		$s->_createNewDatabaseSession();
		$this->assertEquals(2,sizeof($s->getSentCookies())); // two cookies are set without ssl_only option
	}

	function test_resending_session_cookie(){
		global $_COOKIE;

		$_COOKIE = array();
		$s = new SessionStorer();
		$s->_createNewDatabaseSession();
		$this->assertEquals(2,sizeof($s->getSentCookies()));
		$this->_add_cookies($s->getSentCookies(),$_COOKIE);

		$s = new SessionStorer(array("current_time" => CURRENT_TIME + 86400));
		$this->assertEquals(0,sizeof($s->getSentCookies()));

		$permanent = new SessionStorer(array(
			"session_name" => "permanent",
			"cookie_expiration" => 86400 * 365 * 5
		));
		$permanent->_createNewDatabaseSession();
		$this->assertEquals(1,sizeof($permanent->getSentCookies())); // there is already check cookie
		$this->_add_cookies($permanent->getSentCookies(),$_COOKIE);

		$permanent = new SessionStorer(array(
			"session_name" => "permanent",
			"cookie_expiration" => 86400 * 365 * 5,
			"current_time" => CURRENT_TIME + 5,
		));
		$this->assertEquals(0,sizeof($permanent->getSentCookies()));

		// ... after a day
		$permanent = new SessionStorer(array(
			"session_name" => "permanent",
			"cookie_expiration" => 86400 * 365 * 5,
			"current_time" => CURRENT_TIME + 86400,
		));
		$this->assertEquals(1,sizeof($sent_cookies = $permanent->getSentCookies()));
		$this->assertEquals("permanent",$sent_cookies[0][0]); // ...the session cookie was sent again
		$this->_add_cookies($sent_cookies,$_COOKIE);

		// ... after 5 seconds
		$permanent = new SessionStorer(array(
			"session_name" => "permanent",
			"cookie_expiration" => 86400 * 365 * 5,
			"current_time" => CURRENT_TIME + 86400 + 5
		));
		$this->assertEquals(0,sizeof($sent_cookies = $permanent->getSentCookies())); // not this time

		// ... after 20 minutes
		$permanent = new SessionStorer(array(
			"session_name" => "permanent",
			"cookie_expiration" => 86400 * 365 * 5,
			"current_time" => CURRENT_TIME + 86400 + 5 + 60 * 20
		));
		$this->assertEquals(1,sizeof($sent_cookies = $permanent->getSentCookies())); // but now again
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
		global $_COOKIE;

		$_COOKIE = array();
		$s = new SessionStorer();
		$this->assertEquals(null,$s->getSecretToken());
		$this->assertEquals(1,sizeof($s->getSentCookies())); // testing cookies

		$_COOKIE = array();
		$s->_createNewDatabaseSession();
		$this->assertEquals(true,strlen($token = $s->getSecretToken())>0);
		$this->assertEquals(2,sizeof($sent_cookies = $s->getSentCookies())); // testing cookies + session cookie
		$this->_add_cookies($sent_cookies,$_COOKIE);
		$this->assertTrue(!!strlen($s->getSecretToken()));
		$this->assertEquals("session",$s->getSessionName());
		$this->assertEquals("default",$s->getSection());

		$s2 = new SessionStorer();
		$this->assertEquals("session",$s2->getSessionName());
		$this->assertEquals(0,sizeof($sent_cookies = $s2->getSentCookies()));
		$this->assertEquals($s->getSecretToken(),$s2->getSecretToken());
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

	function test__getCookieDomain(){
		global $HTTP_REQUEST;

		$HTTP_REQUEST->setHttpHost("www.example.org");
		$ss = new SessionStorer();
		$this->assertEquals(".example.org",$ss->_getCookieDomain(true)); // automation
		$this->assertEquals(null,$ss->_getCookieDomain(false));
		$this->assertEquals(".example.org",$ss->_getCookieDomain(".example.org"));
		$this->assertEquals(".www.example.org",$ss->_getCookieDomain(".www.example.org"));

		$HTTP_REQUEST->setHttpHost("beta.admin.internal.example.org");
		$ss = new SessionStorer();
		$this->assertEquals(".beta.admin.internal.example.org",$ss->_getCookieDomain(true)); // no automation in this case
		$this->assertEquals(null,$ss->_getCookieDomain(false));
		$this->assertEquals(".example.org",$ss->_getCookieDomain(".example.org"));
		$this->assertEquals(".admin.internal.example.org",$ss->_getCookieDomain(".admin.internal.example.org"));
		$this->assertEquals(".beta.admin.internal.example.org",$ss->_getCookieDomain(".beta.admin.internal.example.org"));
		$this->assertEquals(null,$ss->_getCookieDomain(".zeus.beta.admin.internal.example.org"));
		$this->assertEquals(null,$ss->_getCookieDomain(".other.domain.com"));

		// IPv4 address
		$HTTP_REQUEST->setHttpHost("10.20.30.40");
		$ss = new SessionStorer();
		$this->assertEquals(null,$ss->_getCookieDomain());
		$this->assertEquals(null,$ss->_getCookieDomain(true));
		$this->assertEquals(null,$ss->_getCookieDomain(false));
		$this->assertEquals(null,$ss->_getCookieDomain(".www.example.org"));

		// IPv4 address with a port
		$HTTP_REQUEST->setHttpHost("10.20.30.40:81");
		$ss = new SessionStorer();
		$this->assertEquals(null,$ss->_getCookieDomain());
		$this->assertEquals(null,$ss->_getCookieDomain(true));
		$this->assertEquals(null,$ss->_getCookieDomain(false));
		$this->assertEquals(null,$ss->_getCookieDomain(".www.example.org"));

		// IPv6 address
		$HTTP_REQUEST->setHttpHost("1762:0:0:0:0:B03:1:AF18");
		$ss = new SessionStorer();
		$this->assertEquals(null,$ss->_getCookieDomain());
		$this->assertEquals(null,$ss->_getCookieDomain(true));
		$this->assertEquals(null,$ss->_getCookieDomain(false));
		$this->assertEquals(null,$ss->_getCookieDomain(".www.example.org"));

		// IPv6 address
		$HTTP_REQUEST->setHttpHost("::1");
		$ss = new SessionStorer();
		$this->assertEquals(null,$ss->_getCookieDomain());
		$this->assertEquals(null,$ss->_getCookieDomain(true));
		$this->assertEquals(null,$ss->_getCookieDomain(false));
		$this->assertEquals(null,$ss->_getCookieDomain(".www.example.org"));
	}

	function test__garbageCollection(){
		global $_COOKIE;

		// gonna test standard sessions with cookie_expiration=0

		$_COOKIE = array();
		$first = new SessionStorer();
		$first->_createNewDatabaseSession();
		$first->writeValue("logged_user","mr.fox");
		$cookies_sent_by_first = $first->getSentCookies();

		// after two days the first session shall be deleted by garbage collection
		$_COOKIE = array();
		$second = new SessionStorer(array("current_time" => CURRENT_TIME + 2 * 86400));
		$second->_createNewDatabaseSession();
		$second->writeValue("logged_user","no.brain");
		$cookies_sent_by_second = $second->getSentCookies();

		$this->assertTrue($first->getSecretToken()!=$second->getSecretToken());

		// after another hour - the first session is not here
		$_COOKIE = array();
		$this->_add_cookies($cookies_sent_by_first,$_COOKIE);
		$session = new SessionStorer(array("current_time" => CURRENT_TIME + 2*86400 + 60*60));
		$this->assertEquals(null,$session->getSecretToken());
		$this->assertEquals(null,$session->readValue("logged_user"));

		// after another hour - the second session exists
		$_COOKIE = array();
		$this->_add_cookies($cookies_sent_by_second,$_COOKIE);
		$session = new SessionStorer(array("current_time" => CURRENT_TIME + 2*86400 + 60*60 + 60*60));
		$this->assertEquals($second->getSecretToken(),$session->getSecretToken());
		$this->assertEquals("no.brain",$second->readValue("logged_user"));

		// gonna test long lasting sessions with cookie_expiration>0

		$_COOKIE = array();
		$persistent_1st = new SessionStorer(array("session_name" => "persisten", "cookie_expiration" => 86400*365));
		$persistent_1st->_createNewDatabaseSession();
		$persistent_1st->writeValue("logged_user","we.too.long");
		$cookies_sent_by_persistent_1st = $persistent_1st->getSentCookies();

		// after 10 days
		$_COOKIE = array();
		$persistent_2nd = new SessionStorer(array("session_name" => "persisten", "cookie_expiration" => 86400*365, "current_time" => CURRENT_TIME + 10*86400));
		$persistent_2nd->_createNewDatabaseSession();
		$persistent_2nd->writeValue("logged_user","bob.the.bomber");
		$cookies_sent_by_persistent_2nd = $persistent_2nd->getSentCookies();

		// after 201 days - the first session still exists and it's last_access is gonna be updated
		$_COOKIE = array();
		$this->_add_cookies($cookies_sent_by_persistent_1st,$_COOKIE);
		$session = new SessionStorer(array("session_name" => "persisten", "cookie_expiration" => 86400*365, "current_time" => CURRENT_TIME + 201*86400));
		$this->assertEquals($persistent_1st->getSecretToken(),$session->getSecretToken());
		$this->assertEquals("we.too.long",$session->readValue("logged_user"));

		// after 500 days - the first session exists due to newer last_access...
		$_COOKIE = array();
		$this->_add_cookies($cookies_sent_by_persistent_1st,$_COOKIE);
		$session = new SessionStorer(array("session_name" => "persisten", "cookie_expiration" => 86400*365, "current_time" => CURRENT_TIME + 500*86400));
		$this->assertEquals($persistent_1st->getSecretToken(),$session->getSecretToken());
		$this->assertEquals("we.too.long",$session->readValue("logged_user"));

		// ... but the 2nd session has been deleted
		$_COOKIE = array();
		$this->_add_cookies($cookies_sent_by_persistent_2nd,$_COOKIE);
		$session = new SessionStorer(array("session_name" => "persisten", "cookie_expiration" => 86400*365, "current_time" => CURRENT_TIME + 500*86400));
		$this->assertEquals(null,$session->getSecretToken());
		$this->assertEquals(null,$session->readValue("logged_user"));
	}

	function test__isTimeToUpdateLastAccess(){
		$time = CURRENT_TIME;

		$s = new SessionStorer(array("current_time" => $time));

		$this->assertEquals(false,$s->_isTimeToUpdateLastAccess(date("Y-m-d H:i:s",$time)));
		$this->assertEquals(false,$s->_isTimeToUpdateLastAccess(date("Y-m-d H:i:s",$time - 60)));
		$this->assertEquals(true,$s->_isTimeToUpdateLastAccess(date("Y-m-d H:i:s",$time - 60 * 10)));

		$trues = $falses = 0;
		for($i=0;$i<100;$i++){
			$ret = $s->_isTimeToUpdateLastAccess(date("Y-m-d H:i:s",$time - 60 * 5));
			if($ret){ $trues++; }else{ $falses++; }
		}
		$this->assertTrue($trues>0);
		$this->assertTrue($falses>0);
	}

	function test__obtainSessionIdAndSecurityPairs(){
		// initial check
		$s = new SessionStorer();

		$this->assertEquals("session",$s->getCookieName());
	
		// --
		$request = new HTTPRequest();
		$request->setHeader("Cookie","");
		$request->setCookieVar("session",null);
		$s = new SessionStorer(array(
			"request" => $request,
		));
		$this->assertEquals(array(),$s->_obtainSessionIdAndSecurityPairs());

		// --
		$request->setCookieVar("session","invalid_val");
		$s = new SessionStorer(array(
			"request" => $request,
		));
		$this->assertEquals(array(),$s->_obtainSessionIdAndSecurityPairs());

		// --
		$request->setCookieVar("session","123.abcdefghijklmopqrstuvwxyz0123456");

		$s = new SessionStorer(array(
			"request" => $request,
		));
		$this->assertEquals(array("123.abcdefghijklmopqrstuvwxyz0123456" => array("id" => 123, "security" => "abcdefghijklmopqrstuvwxyz0123456")),$s->_obtainSessionIdAndSecurityPairs());

		$request->setHeader("Cookie","check=1490347093; session=4881.a13fhJVULIxDlrnp97ogE8K4bmc0twQF; session=invalid_val; session2=681433.ExdUe0wl12pTKysc26ShP27IKR93j0vW; session=14227.J7vPy5fhDVcRd3KEnHeQrsqCSbFO6xal; ");

		$this->assertEquals(array(
			"123.abcdefghijklmopqrstuvwxyz0123456" => array("id" => 123, "security" => "abcdefghijklmopqrstuvwxyz0123456"),
			"4881.a13fhJVULIxDlrnp97ogE8K4bmc0twQF" => array("id" => 4881, "security" => "a13fhJVULIxDlrnp97ogE8K4bmc0twQF"),
			"14227.J7vPy5fhDVcRd3KEnHeQrsqCSbFO6xal" => array("id" => 14227, "security" => "J7vPy5fhDVcRd3KEnHeQrsqCSbFO6xal"),
		),$s->_obtainSessionIdAndSecurityPairs());
	}

	function test_default_cookies_options(){
		$defaults = HTTPCookie::DefaultOptions();

		$response = new HTTPResponse();
		$storer = new SessionStorer(array("response" => $response));
		$cookies = $response->getCookies();
		$check_cookie = $cookies[0];

		$this->assertEquals(false,$check_cookie->isSecure());
		$this->assertEquals("",$check_cookie->getSameSite());

		HTTPCookie::DefaultOptions(array(
			"secure" => true,
			"samesite" => "None",
		));

		$response = new HTTPResponse();
		$storer = new SessionStorer(array("response" => $response));
		$cookies = $response->getCookies();
		$check_cookie = $cookies[0];

		$this->assertEquals(true,$check_cookie->isSecure());
		$this->assertEquals("None",$check_cookie->getSameSite());

		HTTPCookie::DefaultOptions($defaults);
	}


	function _add_cookies($send_cookies,&$store){
		foreach($send_cookies as $item){
			$store[$item[0]] = $item[1];
		}
	}
}

<?php
class tc_http_cookie extends tc_base{

	function test(){
		$vals = array(
			"name" => "session",
			"value" => "1234abcd",
			"expire" => 0,
			"path" => "/",
			"domain" => "",
			"secure" => false,
			"httponly" => false,
		);
		$c = new HTTPCookie($vals["name"],$vals["value"]);
		$this->_checkVals($c,$vals);

		$c->setSecure();
		$vals["secure"] = true;
		$this->_checkVals($c,$vals);

		$c->setHttponly();
		$c->setSecure(false);
		$vals["httponly"] = true;
		$vals["secure"] = false;
		$this->_checkVals($c,$vals);
	}

	function test_isExpired(){
		$cookie = new HTTPCookie("cookie","val");
		$this->assertEquals(false,$cookie->isExpired());

		$cookie->setExpire(0);
		$this->assertEquals(false,$cookie->isExpired());

		$cookie->setExpire(time() + 60 * 60 * 24);
		$this->assertEquals(false,$cookie->isExpired());

		$cookie->setExpire(time() - 60 * 60 * 24);
		$this->assertEquals(true,$cookie->isExpired());
	}

	function test_isDesignatedFor(){
		$request = new HTTPRequest();
		$this->assertEquals(false,$request->sslActive());

		$cookie = new HTTPCookie("cookie","val");
		$this->assertEquals(true,$cookie->isDesignatedFor($request));

		$cookie->setSecure();
		$this->assertEquals(false,$cookie->isDesignatedFor($request));

		$GLOBALS["_SERVER"]["HTTPS"] = "on"; // TODO: do it better
		$this->assertEquals(true,$request->sslActive());
		$this->assertEquals(true,$cookie->isDesignatedFor($request));
	}

	function _checkVals($cookie,$vals){
		foreach($vals as $k => $v){
			$camel = String4::ToObject($k)->camelize();
			$method = in_array($k,array("secure","httponly"))	? "is$camel" : "get$camel";

			$this->assertEquals($v,$cookie->$method());
		}
	}
}


<?php
class TcSessionStorer extends TcBase{
	function test_cookiesEnabled(){
		global $_COOKIE;

		$s = new SessionStorer();

		$_COOKIE = array();
		$this->assertFalse($s->cookiesEnabled());

		$_COOKIE = array("key" => "val");
		$this->assertTrue($s->cookiesEnabled());
	}
}

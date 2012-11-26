<?php
class TcBase extends TcSuperbase{
	function setUp(){
		global $_COOKIE;

		if(!isset($_COOKIE)){ $_COOKIE = array(); }
		$_COOKIE = array();
	}
}

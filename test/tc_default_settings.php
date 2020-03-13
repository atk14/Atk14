<?php
class TcDefaultSettings extends TcBase {

	function test(){
		$this->assertEquals("http://www.our-awesome-website.com/",ATK14_APPLICATION_URL);
	}
}

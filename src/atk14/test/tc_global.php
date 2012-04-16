<?php
class TcGlobal extends TcBase{
	function test(){
		$global = new Atk14Global();

		$this->assertEquals("cs",$global->getDefaultLang());
		$this->assertEquals("cs",$global->getLang());

		$global->setValue("lang","en");

		$this->assertEquals("cs",$global->getDefaultLang());
		$this->assertEquals("en",$global->getLang());
	}
}

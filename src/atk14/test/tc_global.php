<?php
class TcGlobal extends TcBase{

	function test_paths(){
		$global = new Atk14Global();

		$this->assertEquals(__DIR__ . "/",$global->getDocumentRoot());
		$this->assertEquals(__DIR__ . "/app/",$global->getApplicationPath());
		$this->assertEquals(__DIR__ . "/public/",$global->getPublicRoot());

		$this->assertEquals("/",$global->getBaseHref());
		$this->assertEquals("/public/",$global->getPublicBaseHref());
	}

	function test_locales(){
		$global = new Atk14Global();

		$this->assertEquals(array("cs","en","sk"),$global->getSupportedLangs()); // see config/locale.yml

		$this->assertEquals("cs",$global->getDefaultLang());
		$this->assertEquals("cs",$global->getLang());

		$global->setValue("lang","en");

		$this->assertEquals("cs",$global->getDefaultLang());
		$this->assertEquals("en",$global->getLang());
	}

	function test_getConfig(){
		$global = new Atk14Global();

		$this->assertEquals(array(
			"name" => "Magic Plugin",
			"purpose" => "Testing"
		),$global->getConfig("magic_plugin"));

		$this->assertEquals(null,$global->getConfig("not_existing_config"));

		// this must load the config from local_config/fruits.yml
		$this->assertEquals(array('banana','strawberry'),$global->getConfig("fruits"));
	}
}

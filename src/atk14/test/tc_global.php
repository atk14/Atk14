<?php
class TcGlobal extends TcBase{

	function test_paths(){
		$global = Atk14Global::GetInstance();

		$this->assertEquals(__DIR__ . "/",$global->getDocumentRoot());
		$this->assertEquals(__DIR__ . "/app/",$global->getApplicationPath());
		$this->assertEquals(__DIR__ . "/public/",$global->getPublicRoot());

		$this->assertEquals("/",$global->getBaseHref());
		$this->assertEquals("/public/",$global->getPublicBaseHref());
	}

	function test_locales(){
		$global = Atk14Global::GetInstance();

		$this->assertEquals(["cs","en","sk"],$global->getSupportedLangs()); // see config/locale.yml

		$this->assertEquals("cs",$global->getDefaultLang());
		$this->assertEquals("cs",$global->getLang());

		$global->setValue("lang","en");

		$this->assertEquals("cs",$global->getDefaultLang());
		$this->assertEquals("en",$global->getLang());
	}

	function test_getConfig(){
		$global = Atk14Global::GetInstance();

		$this->assertEquals([
			"name" => "Magic Plugin",
			"purpose" => "Testing"
		],$global->getConfig("magic_plugin"));

		$this->assertEquals(null,$global->getConfig("not_existing_config"));

		// this must load the config from local_config/fruits.yml
		$this->assertEquals(['banana','strawberry'],$global->getConfig("fruits"));
		$this->assertEquals(['banana','strawberry'],$global->getConfig("fruits.yml"));

		// jsons
		$this->assertEquals(["red" => "#ff0000", "blue" => "#0000ff", "green" => "#00ff00"],$global->getConfig("colors"));
		$this->assertEquals(["red" => "#ff0000", "blue" => "#0000ff", "green" => "#00ff00"],$global->getConfig("colors.json"));
		$this->assertEquals([],$global->getConfig("empty_array"));

		// subfolders
		$this->assertEquals([0,1.25,2.5],$global->getConfig("theme/spacers"));
		$this->assertEquals(["primary" => "#334455", "warning" => "#ff0000"],$global->getConfig("theme/colors")); // this one is redefined in local_config/theme/colors.json

		// PHP configs

		// file config/animals.php
		$this->assertEquals(["lamb","lion"],$global->getConfig("animals"));

		// file local_config/heroes.php, not config/heroes.php
		$this->assertEquals(["Sandokan","Falcon_guardian_of_the_night","Indiana_Jones"],$global->getConfig("heroes"));
	}

	function test_setConfig(){
		$global = Atk14Global::GetInstance();

		$this->assertEquals(["cs","en","sk"],$global->getSupportedLangs()); // see config/locale.yml

		$locale = $global->getConfig("locale");
		$locale["hu"] = [
			"LANG" => "hu_HU.UTF-8",
		];
		$global->setConfig("locale",$locale);

		$this->assertEquals(["cs","en","sk","hu"],$global->getSupportedLangs());

		//

		$this->assertEquals(null,$global->getConfig("main_colors"));
		
		$global->setConfig("main_colors",[
			"primary" => "red",
			"secondary" => "blue",
			"background" => "white",
		]);

		$this->assertEquals([
			"primary" => "red",
			"secondary" => "blue",
			"background" => "white",
		],$global->getConfig("main_colors"));
	}
}

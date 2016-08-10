<?php
class tc_master_api extends tc_base{
	function test_credit_info(){
		$data = "
---
command: credit info
params: []
";
		$ar = miniYAML::Load($data);
		$this->assertEquals("credit info",$ar["command"]);

$data = '
status: success
message: Ok
data: 
  CZK: "581.22"
  EUR: "0"

';
		$ar = miniYAML::Load($data);
		$this->assertEquals("success",$ar["status"]);
		$this->assertEquals("Ok",$ar["message"]);
		$this->assertTrue(is_array($ar["data"]));
		$this->assertEquals(2,sizeof($ar["data"]));
		$this->assertEquals("581.22",$ar["data"]["CZK"]);
		$this->assertEquals("0",$ar["data"]["EUR"]);
	}



	function test_list_domains(){
		$data = "
---
command: list domains
params: []";
		$ar = miniYAML::Load($data);

		$this->assertEquals("list domains",$ar["command"]);
		$this->assertTrue(is_array($ar["params"]));
		$this->assertEquals(0,sizeof($ar["params"]));

		$data = '
---
status: success
message: Ok
data: 
  2.5.0.6.9.2.3.0.6.0.2.4.e164.arpa: 
    idacc: GR:TOMEK
    iddealer: GR:TOMEK-JAROMIR
    expiry_date: 2008-12-12
    auto_period: "1"
  58cgmz1wav.cz: 
    idacc: GR:TOMEK
    iddealer: GR:TOMEK-JAROMIR
    expiry_date: 2008-11-13
    auto_period: "1"
  b4do5ul3sn.cz: 
    idacc: GR:TOMEK
    iddealer: GR:TOMEK-JAROMIR
    expiry_date: 2008-11-13
    auto_period: "1"
  cdyz1uw29m.cz: 
    idacc: GR:TOMEK
    iddealer: GR:TOMEK-JAROMIR
    expiry_date: 2008-11-13
    auto_period: "1"
  e-logistika.info: 
    idacc: GR:MINO
    iddealer: GR:TOMEK-JAROMIR
    expiry_date: 2008-11-28
    auto_period: "2"

';
		$ar = miniYAML::Load($data);

		$this->assertEquals("success",$ar["status"]);
		$this->assertEquals("Ok",$ar["message"]);
		$this->assertTrue(is_array($ar["data"]));
		$this->assertEquals(5,sizeof($ar["data"]));

		$this->assertEquals(4,sizeof($ar["data"]["2.5.0.6.9.2.3.0.6.0.2.4.e164.arpa"]));
		$this->assertEquals("GR:TOMEK",$ar["data"]["2.5.0.6.9.2.3.0.6.0.2.4.e164.arpa"]["idacc"]);
		$this->assertEquals("GR:TOMEK-JAROMIR",$ar["data"]["2.5.0.6.9.2.3.0.6.0.2.4.e164.arpa"]["iddealer"]);
		$this->assertEquals("2008-12-12",$ar["data"]["2.5.0.6.9.2.3.0.6.0.2.4.e164.arpa"]["expiry_date"]);
		$this->assertEquals("1",$ar["data"]["2.5.0.6.9.2.3.0.6.0.2.4.e164.arpa"]["auto_period"]);

		$this->assertEquals(4,sizeof($ar["data"]["e-logistika.info"]));
		$this->assertEquals("GR:MINO",$ar["data"]["e-logistika.info"]["idacc"]);
		$this->assertEquals("GR:TOMEK-JAROMIR",$ar["data"]["e-logistika.info"]["iddealer"]);
		$this->assertEquals("2008-11-28",$ar["data"]["e-logistika.info"]["expiry_date"]);
		$this->assertEquals("2",$ar["data"]["e-logistika.info"]["auto_period"]);
	}

	function test_check_domains_availability(){
		$data = "
---
command: check domains availability
params: 
  domains: 
  - domainmaster.cz
  - generalregistry.cz
  - surely-free-domain.cz
  - wole.com
  - x
";
		$ar = miniYAML::Load($data);
		$this->assertEquals("check domains availability",$ar["command"]);
		$this->assertTrue(is_array($ar["params"]));
		$this->assertEquals(1,sizeof($ar["params"]));
		$this->assertTrue(is_array($ar["params"]["domains"]));
		$this->assertEquals(5,sizeof($ar["params"]["domains"]));
		$this->assertEquals(array("domainmaster.cz","generalregistry.cz","surely-free-domain.cz","wole.com","x"),$ar["params"]["domains"]);

		$data = '
---
status: success
message: Ok
data: 
  domainmaster.cz: "no"
  generalregistry.cz: "no"
  surely-free-domain.cz: "yes"
  wole.com: "?"
  x: "?"

';
		$ar = miniYAML::Load($data);
		$this->assertEquals("success",$ar["status"]);
		$this->assertEquals("Ok",$ar["message"]);
		$this->assertEquals(array(
			"domainmaster.cz" => "no",
			"generalregistry.cz" => "no",
			"surely-free-domain.cz" => "yes",
			"wole.com" => "?",
			"x" => "?"
		),$ar["data"]);
	}

	function test_info_domain(){
		$data = "
---
command: show domain
params: 
  domain: plovarna.cz
		";
		$ar = miniYAML::Load($data);
		$this->assertEquals("show domain",$ar["command"]);
		$this->assertEquals("plovarna.cz",$ar["params"]["domain"]);

		$data = "
---
status: success
message: Ok
data: 
  domain: plovarna.cz
  registrant: SB:DS-S-02001
  nsset: NSS:DS-S-02001:1
  admin: 
  - TOMEK-JAROMIR
  - DS-C-02001
  tempcontact: []

  registrar: REG-GENREG
  create_date: 2001-01-10
  expiry_date: 2014-01-11
";
	 $ar = miniYAML::Load($data);
	 $this->assertEquals("success",$ar["status"]);
	 $this->assertEquals("plovarna.cz",$ar["data"]["domain"]);
	 $this->assertEquals("SB:DS-S-02001",$ar["data"]["registrant"]);
	 $this->assertEquals("NSS:DS-S-02001:1",$ar["data"]["nsset"]);
	 $this->assertEquals("TOMEK-JAROMIR",$ar["data"]["admin"][0]);
	 $this->assertEquals("DS-C-02001",$ar["data"]["admin"][1]);
	 $this->assertTrue(is_array($ar["data"]["tempcontact"]));
	 $this->assertEquals(0,sizeof($ar["data"]["tempcontact"]));
	 $this->assertEquals("REG-GENREG",$ar["data"]["registrar"]);
	 $this->assertEquals("2001-01-10",$ar["data"]["create_date"]);
	 $this->assertEquals("2014-01-11",$ar["data"]["expiry_date"]);
	}

  function test_register_domain(){
$data = '
---
command: register cz domain
params: 
  domain: minishop.cz
  nsset: NSS:SUB100000001-ZONER:1
  registrant: SB:SUB000026973-ZONER
  admin: MASC
  idacc: GR:MASC
  iddealer: ""
  period: "1"
';
    $ar = miniYAML::Load($data);

    $this->assertEquals(array(
      "command" => "register cz domain",
      "params" => array(
        "domain" => "minishop.cz",
        "nsset" => "NSS:SUB100000001-ZONER:1",
        "registrant" => "SB:SUB000026973-ZONER",
        "admin" => "MASC",
        "idacc" => "GR:MASC",
        "iddealer" => "",
        "period" => "1"
      )
    ),$ar);
  }

	function test_register_eu_contact(){
		$data = '
---
command: register eu contact
params: 
  name: Karel Prokupek
  company: Prokop Buben, s.r.o.
  e-mail: karel@fuzzymail.com
  id: EU:KAREL-PROKUPEK-2
  phone: "+420.603111222"
  fax-no: "+420.2111223"
  vat: CZ1234567
  street-1: Velenicka 22
  street-2: Za kulturnim domem
  street-3: ""
  city: Liberec
  zip: 123 45
  state: ""
  country: cz
  lang: cs
  password-plain: tajnyPRISTUP
  password-md5: ""
  password-crypt: ""
';

    $ar = miniYAML::Load($data);

    $this->assertEquals(array(
      "command" => "register eu contact",
      "params" => array(
				"name" => "Karel Prokupek",
				"company" => "Prokop Buben, s.r.o.",
				"e-mail" => "karel@fuzzymail.com",
				"id" => "EU:KAREL-PROKUPEK-2",
				"phone" => "+420.603111222",
				"fax-no" => "+420.2111223",
				"vat" => "CZ1234567",
				"street-1" => "Velenicka 22",
				"street-2" => "Za kulturnim domem",
				"street-3" => "",
				"city" => "Liberec",
				"zip" => "123 45",
				"state" => "",
				"country" => "cz",
				"lang" => "cs",
				"password-plain" => "tajnyPRISTUP",
				"password-md5" => "",
				"password-crypt" => "",
      )
    ),$ar);
	
	}

}
?>

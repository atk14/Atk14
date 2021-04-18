<?php
class tc_miniyaml extends tc_base{

	function test_read_hash_array(){
		$data = "
---
key1: value1
key2: value2
		";
		$ar = miniYAML::Load($data);
		$this->assertTrue(is_array($ar));
		$this->assertEquals(2,sizeof($ar));
		$this->assertEquals("value1",$ar["key1"]);
		$this->assertEquals("value2",$ar["key2"]);

	}

	function test_read_indexed_array(){
		$data = "
---
- jedna
- dve
- tri
		";
		$ar = miniYAML::Load($data);
		$this->assertTrue(is_array($ar));
		$this->assertEquals(3,sizeof($ar));
		$this->assertEquals("jedna",$ar[0]);
		$this->assertEquals("dve",$ar[1]);
		$this->assertEquals("tri",$ar[2]);
	}

	function test_read_array(){
		$data = "
---
people: everyone
fruits:
- apple
- orange
- lemon
vegetables:
- potatoe
- carrot
animals: none
		";
		$ar = miniYAML::Load($data);
		$this->assertTrue(is_array($ar));
		$this->assertEquals(4,sizeof($ar));
		$this->assertEquals("everyone",$ar["people"]);
		$this->assertTrue(is_array($ar["fruits"]));
		$this->assertEquals(3,sizeof($ar["fruits"]));
		$this->assertEquals("apple",$ar["fruits"][0]);
		$this->assertEquals("orange",$ar["fruits"][1]);
		$this->assertEquals("lemon",$ar["fruits"][2]);
		$this->assertTrue(is_array($ar["vegetables"]));
		$this->assertEquals(2,sizeof($ar["vegetables"]));
		$this->assertEquals("potatoe",$ar["vegetables"][0]);
		$this->assertEquals("carrot",$ar["vegetables"][1]);
		$this->assertEquals("none",$ar["animals"]);
	}

  function test_read_mixed_array(){
    $data = "
- element 1
- - element 2.1
  - element 2.2
- - element 3.1
  - element 3.2
- key1: val1
  key2: val2
  key3:
  - el1
  - el2
";
    $ar = miniYAML::Load($data);
    $this->assertEquals(array(
      "element 1",
      array("element 2.1","element 2.2"),
      array("element 3.1","element 3.2"),
      array(
       "key1" => "val1",
       "key2" => "val2",
       "key3" => array("el1","el2")
      ),
    ),$ar);
  }

	function test_dump_array(){
		$ar = array("a","b","c");
		$this->assertEquals('---
- a
- b
- c',trim(miniYAML::Dump($ar)));
	}

	function test_dump_colon(){
		$hash = array(
			"message" => "error: oups",
			"detailed_info" => "detailed error: Oups!!",
			"a_char" => ":",
			"a_safe_one" => "x:y",
			"another_safe_one" => "x::y",
			"a_bad_tail" => "bad:"
		);
		$this->assertEquals('---
message: "error: oups"
detailed_info: "detailed error: Oups!!"
a_char: ":"
a_safe_one: x:y
another_safe_one: x::y
a_bad_tail: "bad:"',trim(miniYAML::Dump($hash)));

		// --
		$ar = array(
			"error: oups",
			"detailed error: Oups!!",
			":",
			"x:y",
			"x::y",
			"bad:"
		);

		$this->assertEquals('---
- "error: oups"
- "detailed error: Oups!!"
- ":"
- x:y
- x::y
- "bad:"',trim(miniYAML::Dump($ar)));
	}

	function test_dump_empty_array(){
		$hash = array(
			"empty_array" => array()
		);
		$dump = miniYAML::Dump($hash);
		$this->assertEquals("---\nempty_array: []\n\n",$dump);

		$hash = array(
			"params" => array(
				"domain" => "plovarna.cz",
				"tempcontact" => array(),
				"nsset" => "PLOVARNA",
			)
		);
		$dump = miniYAML::Dump($hash);
		$this->assertEquals("---
params: 
  domain: plovarna.cz
  tempcontact: []

  nsset: PLOVARNA
",$dump);
		
	}

	function test_read_empty_array(){
		$data = "
---
empty_array: []

";
		$ar = miniYAML::Load($data);
		$this->assertTrue(is_array($ar));
		$this->assertTrue(is_array($ar["empty_array"]));
		$this->assertEquals(0,sizeof($ar["empty_array"]));
								
	}

	function test_cut_out_block(){
		$yaml = new miniYAML();
		$data = array(
			"line 1",
			"  - - line 2",
			"    - line 3",
			"line 4"
		);

		$ar = $yaml->_cutOutBlock(0,0,$data);
		$this->assertEquals($data,$ar);
		$ar = $yaml->_cutOutBlock_Stripped(0,0,$data);
		$this->assertEquals($data,$ar);

		$ar = $yaml->_cutOutBlock(1,2,$data);
		$this->assertEquals(array("  - - line 2","    - line 3"),$ar);
		$ar = $yaml->_cutOutBlock_Stripped(1,2,$data);
		$this->assertEquals(array("- - line 2","  - line 3"),$ar);

		$ar = $yaml->_cutOutBlock(1,4,$data);
		$this->assertEquals(array("    - line 2","    - line 3"),$ar);
		$ar = $yaml->_cutOutBlock_Stripped(1,4,$data);
		$this->assertEquals(array("- line 2","- line 3"),$ar);

		$ar = $yaml->_cutOutBlock(2,6,$data);
		$this->assertEquals(array("      line 3"),$ar);
		$ar = $yaml->_cutOutBlock_Stripped(2,6,$data);
		$this->assertEquals(array("line 3"),$ar);
	}

	function test_colon_issue(){
		$yaml = new miniYAML();

		$data = "
---
contact-tech: 
- EU:TOMEK-JAROMIR
- EU:TOMEK
";
		$ar = miniYAML::Load($data);
		$this->assertEquals(array(
			"contact-tech" => array(
				"EU:TOMEK-JAROMIR",
				"EU:TOMEK"
			)
		),$ar);

		$data = '
--- 
contact-tech: 
- EU:TOMEK-JAROMIR
- EU:TOMEK
- EU: JARKA
- "EU: MARTA"
';

		$ar = miniYAML::Load($data);
		$this->assertEquals(array(
			"contact-tech" => array(
				"EU:TOMEK-JAROMIR",
				"EU:TOMEK",
				array("EU" => "JARKA"),
				"EU: MARTA"
			)
		),$ar);

		$this->assertEquals(trim('
---
contact-tech: 
- EU:TOMEK-JAROMIR
- EU:TOMEK
- EU: JARKA
- "EU: MARTA"
'),trim(miniYAML::Dump($ar)));
	}

	function test_interpret_php(){
$data = '
---
key1: <?php echo "value 1"?>

key2: <?php echo "value 2"?>';
		$yaml = miniYAML::InterpretPHP($data);
		$this->assertEquals("---\nkey1: value 1\nkey2: value 2",$yaml);

		$ar = miniYAML::Load($data,array("interpret_php" => true));
		$this->assertEquals(array("key1" => "value 1", "key2" => "value 2"),$ar);
$data = '
---
<?php for($i=1;$i<5;$i++){ ?>
<?php echo "key$i: value $i";?>

<?php } ?>
';
		$ar = miniYAML::Load($data,array("interpret_php" => true));
		$this->assertEquals(array(
			"key1" => "value 1",
			"key2" => "value 2",
			"key3" => "value 3",
			"key4" => "value 4",
		),$ar);

$data = '
---
key1: <?php echo $hodnota_1?>


key2: <?php echo $hodnota_2?>
';
		$ar = miniYAML::Load($data,array("interpret_php" => true));
		$this->assertEquals(array(
			"key1" => "",
			"key2" => ""
		),$ar);

		$ar = miniYAML::Load($data,array(
			"interpret_php" => true,
			"values" => array(
				"hodnota_1" => "yes",
				"hodnota_2" => "don't know"
			)
		));
		$this->assertEquals(array(
			"key1" => "yes",
			"key2" => "don't know"
		),$ar);
	}

	function test_white_char_exception(){
		$data = "---\nkey: value\n\tkey2: value2";

		try{
			$a = miniYAML::Load($data);
			$this->fail();
		}catch(Exception $e){
			$this->assertContains("token cannot begin with tabulator",$e->getMessage());
		}
	}

	function test_null(){
		$data = '
---
key1: null
key2: NULL
key3: "null"
key4: "NULL"
key5: Null
key6: "Null"
';
		$a = miniYAML::Load($data);
		//
		$this->assertTrue(is_null($a["key1"]));
		$this->assertTrue(is_null($a["key2"]));
		$this->assertTrue($a["key3"]==="null");
		$this->assertTrue($a["key4"]==="NULL");
		$this->assertTrue($a["key5"]==="Null");
		$this->assertTrue($a["key6"]==="Null");

		$a = miniYAML::Load($data,array("nullable" => false));
		//
		$this->assertTrue($a["key1"]==="null");
		$this->assertTrue($a["key2"]==="NULL");
		$this->assertTrue($a["key3"]==="null");
		$this->assertTrue($a["key4"]==="NULL");
		$this->assertTrue($a["key5"]==="Null");
		$this->assertTrue($a["key6"]==="Null");
	}
}

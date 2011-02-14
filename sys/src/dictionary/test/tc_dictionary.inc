<?
class tc_dictionary extends tc_base{
	function test(){
		$dict = new Dictionary(array(
			"key1" => "value1",
			"key2" => "value2",
			"key3" => null
		));

		$this->assertTrue($dict->defined("key1"));
		$this->assertTrue($dict->defined("key2"));
		$this->assertFalse($dict->defined("key3"));
		$this->assertFalse($dict->defined("key4"));

		$this->assertTrue($dict->keyPresents("key1"));
		$this->assertTrue($dict->keyPresents("key2"));
		$this->assertTrue($dict->keyPresents("key3"));
		$this->assertFalse($dict->keyPresents("key4"));

		$this->assertEquals("value1",$dict->getValue("key1"));
		$this->assertEquals("value2",$dict->getValue("key2"));
		$this->assertNull($dict->getValue("key3"));
		$this->assertNull($dict->getValue("key4"));

		$dict->unsetValue("key1");
		$this->assertNull($dict->getValue("key1"));
		$this->assertFalse($dict->keyPresents("key1"));

		$dict->setValue("key4","value4");
		$this->assertEquals("value4",$dict->getValue("key4"));
	}

	function test_shortcut(){
		$dict = new Dictionary(array(
			"key1" => "value1",
			"key2" => "value2",
			"key3" => null
		));

		$this->assertTrue($dict->defined("key1"));
		$this->assertTrue($dict->defined("key2"));
		$this->assertFalse($dict->defined("key3"));
		$this->assertFalse($dict->defined("key4"));

		$this->assertTrue($dict->keyPresents("key1"));
		$this->assertTrue($dict->keyPresents("key2"));
		$this->assertTrue($dict->keyPresents("key3"));
		$this->assertFalse($dict->keyPresents("key4"));

		$this->assertEquals("value1",$dict->g("key1"));
		$this->assertEquals("value2",$dict->g("key2"));
		$this->assertNull($dict->g("key3"));
		$this->assertNull($dict->g("key4"));

		$dict->unsetValue("key1");
		$this->assertNull($dict->g("key1"));
		$this->assertFalse($dict->keyPresents("key1"));

		$dict->s("key4","value4");
		$this->assertEquals("value4",$dict->g("key4"));
	}

	function test_type_definition(){
		$dict = new Dictionary();

		$this->assertNull($dict->getValue("klic"));
		$this->assertNull($dict->getValue("klic","integer"));

		$dict->setValue("klic","33.4");
		$this->assertEquals("33.4",$dict->getValue("klic"));
		$this->assertType("string",$dict->getValue("klic"));

		$this->assertEquals(33,$dict->getValue("klic","integer"));
		$this->assertType("integer",$dict->getValue("klic","integer"));

		$this->assertEquals("33.4",$dict->getValue("klic"));
		$this->assertType("string",$dict->getValue("klic"));
	}

	function test_to_array(){
		$dict = new Dictionary(array(
			"key1" => "value1",
			"key2" => "value2",
		));

		$this->assertEquals(array(
			"key1" => "value1",
			"key2" => "value2",
		),$dict->toArray());
	}

	function test_merge(){
		$dict = new Dictionary(array(
			"color" => "red",
			"width" => "10cm"
		));

		$dict->merge(array("color" => "blue","height" => "12cm"));

		$this->assertEquals("blue",$dict->getValue("color"));
		$this->assertEquals("10cm",$dict->getValue("width"));
		$this->assertEquals("12cm",$dict->getValue("height"));

		$dict2 = new Dictionary(array("width" => "0.10m","height" => "0.12m"));
		$dict->merge($dict2);

		$this->assertEquals("blue",$dict->getValue("color"));
		$this->assertEquals("0.10m",$dict->getValue("width"));
		$this->assertEquals("0.12m",$dict->getValue("height"));

		// mergovani s nullem je osetreno
		$dict = new Dictionary(array("key" => "val"));
		$dict->merge(null);

		$this->assertEquals(array("key" => "val"),$dict->toArray());
	}

	function test_size_and_is_empty(){
		$dict = new Dictionary();
		$this->assertEquals(0,$dict->size());
		$this->assertTrue($dict->isEmpty());

		$dict->s("color","red");
		$this->assertEquals(1,$dict->size());
		$this->assertFalse($dict->isEmpty());

		$dict->s("age","16");
		$this->assertEquals(2,$dict->size());
		$this->assertFalse($dict->isEmpty());

		$dict->s("color","blue");
		$this->assertEquals(2,$dict->size());
		$this->assertFalse($dict->isEmpty());

		$dict->delete("color");
		$this->assertEquals(1,$dict->size());
		$this->assertFalse($dict->isEmpty());
	}

	function test_unshift(){
		$dict = new Dictionary(array("prijmeni" => "tomino"));
		$dict->unshift("jmeno","yarino");

		$this->assertEquals(array("jmeno" => "yarino", "prijmeni" => "tomino"),$dict->toArray());
	}

	function test_copy(){
		$dict = new Dictionary(array("k1" => "v1"));
		$copy = $dict->copy();

		$this->assertEquals(array("k1" => "v1"),$dict->toArray());
		$this->assertEquals(array("k1" => "v1"),$copy->toArray());

		$copy->s("k2","v2");

		$this->assertEquals(array("k1" => "v1"),$dict->toArray());
		$this->assertEquals(array("k1" => "v1","k2" => "v2"),$copy->toArray());
	}

	function test_get_shortcuts(){
		$d = new Dictionary(array(
			"i" => 33,
			"s" => "Hello",
			"a" => array("a","b"),
		));

		$this->assertEquals(33,$d->getInt("i"));
		$this->assertEquals("33",$d->getString("i"));
		$this->assertEquals(array(33),$d->getArray("i")); // Chceme to takto?

		$this->assertEquals(0,$d->getInt("s"));
		$this->assertEquals("Hello",$d->getString("s"));
		$this->assertEquals(array("Hello"),$d->getArray("s")); // Chceme to takto?

		$this->assertNull($d->getInt("ii"));
		$this->assertNull($d->getString("ii"));
		$this->assertNull($d->getArray("ii"));
	}
}
?>

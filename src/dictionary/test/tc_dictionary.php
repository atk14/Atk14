<?php
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

		$this->assertEquals(array("key1","key2","key3"),$dict->getKeys(array("as_hash" => false)));
		$this->assertEquals(array("key1" => "key1","key2" => "key2","key3" => "key3"),$dict->getKeys());

		$dict->unsetValue("key1");
		$this->assertNull($dict->getValue("key1"));
		$this->assertFalse($dict->keyPresents("key1"));

		$dict->setValue("key4","value4");
		$this->assertEquals("value4",$dict->getValue("key4"));

		$this->assertEquals(array("key2" => "key2","key3" => "key3", "key4" => "key4"),$dict->getKeys());
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

		$dict->add("key5","value5");
		$this->assertEquals("value5",$dict->g("key5"));

		$dict->del("key5");
		$this->assertNull($dict->g("key5"));
	}

	function test_type_definition(){
		$dict = new Dictionary();

		$this->assertNull($dict->getValue("klic"));
		$this->assertNull($dict->getValue("klic","integer"));

		$dict->setValue("klic","33.4");
		$this->assertEquals("33.4",$dict->getValue("klic"));
		$this->assertTrue(is_string($dict->getValue("klic")));
		$this->assertFalse(is_int($dict->getValue("klic")));

		$this->assertEquals(33,$dict->getValue("klic","integer"));
		$this->assertFalse(is_string($dict->getValue("klic","integer")));
		$this->assertTrue(is_int($dict->getValue("klic","integer")));

		$this->assertEquals("33.4",$dict->getValue("klic"));
		$this->assertTrue(is_string($dict->getValue("klic")));
		$this->assertFalse(is_int($dict->getValue("klic")));
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
		$this->assertEquals(0,$d->getInt("s")); // TODO: Shouldn't this be null?
		$this->assertEquals("33",$d->getString("i"));
		$this->assertEquals(array(33),$d->getArray("i")); // TODO: Do we really want this? It looks like crap.

		$this->assertEquals(0,$d->getInt("s"));
		$this->assertEquals("Hello",$d->getString("s"));
		$this->assertEquals(array("Hello"),$d->getArray("s")); // TODO: Another crap?

		$this->assertNull($d->getInt("ii"));
		$this->assertNull($d->getString("ii"));
		$this->assertNull($d->getArray("ii"));
	}

	function test_get_bool(){
		$data = array("true","TRUE","Y","y","1","enable","ENABLE","on","123","yup");
		$this->_check_bool($data,true);

		$data = array("","0","-11","no","nope","F","false","nope");
		$this->_check_bool($data,false);
	}

	function test_actc_as_array(){
		$dict = new Dictionary(array("firstname" => "yarrino"));
		$dict["lastname"] = "tomino";
		$this->assertEquals("yarrino",$dict["firstname"]);
		$this->assertEquals("tomino",$dict["lastname"]);
		$this->assertEquals(null,$dict["xxx"]);

		$this->assertEquals(2,sizeof($dict));

		// testing iteration over array items
		$out = array();
		foreach($dict as $key => $value){
			$out[] = $key;
			$out[] = $value;
		}
		$this->assertEquals("firstname,yarrino,lastname,tomino",join(",",$out));

		$dict["very_lastname"] = "coder";
		$this->assertEquals(3,sizeof($dict));

		$dict["very_lastname"] = null;
		$this->assertEquals(array("firstname" => "yarrino", "lastname" => "tomino", "very_lastname" => null),$dict->toArray());

		unset($dict["very_lastname"]);
		$this->assertEquals(array("firstname" => "yarrino", "lastname" => "tomino"),$dict->toArray());

		// numeric keys
		$dict = new Dictionary();
		$dict[] = "figure 1";
		$dict[] = "figure 2";
		$dict[] = "figure 3";
		$this->assertEquals(array(0 => "figure 1", 1 => "figure 2", 2 => "figure 3"),$dict->toArray());

		$dict[1] = "figure #2";
		$dict[] = "figure #4";
		$this->assertEquals(array(0 => "figure 1", 1 => "figure #2", 2 => "figure 3", 3 => "figure #4"),$dict->toArray());

		$dict = new Dictionary();
		$dict["a"] = "a";
		$dict[] = "figure 1";
		$dict["b"] = "b";
		$dict[] = "figure 2";
		$this->assertEquals(array("a" => "a", 0 => "figure 1", "b" => "b", 1 => "figure 2"),$dict->toArray());
	}

	function _check_bool($data,$expected){
		$d_data = array();
		foreach($data as $v){
			$d_data[$v] = $v;
		}
		$d = new Dictionary($d_data);
		foreach($data as $k){
			$this->assertEquals($expected,$d->getBool($k));
		}
	}
}

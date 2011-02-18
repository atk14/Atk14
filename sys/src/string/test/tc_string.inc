<?
class tc_string extends tc_base{
	function test_random_string(){
		$s1 = String::RandomString();
		$s2 = String::RandomString();
		$s3 = String::RandomString(22);

		$this->assertEquals(32,strlen($s1));
		$this->assertEquals(32,strlen($s2));
		$this->assertEquals(22,strlen($s3));

		$this->assertTrue($s1!=$s2);
	}

	function test_instance(){
		$string = "Hello World";
		$stringer = new String($string);

		$this->assertTrue(is_object($stringer));
		$this->assertFalse(is_object($string));

		$this->assertEquals("$string","$stringer");
		$this->assertEquals(11,strlen($stringer));
	}

	function test_replace(){
		$str = new String("Hello World");
		$this->assertEquals("Hello Guys",(string)$str->replace("World","Guys"));

		$this->assertEquals("Hi Guys",(string)$str->replace(array(
			"Hello" => "Hi",
			"World" => "Guys",
		)));

		$this->assertEquals("Hello World",(string)$str->replace(array()));
	}

	function test_match(){
		$domain = new String("domain.cz");
		$this->assertEquals(true,(bool)$domain->match("/.*\\.cz$/"));
		$this->assertEquals(false,(bool)$domain->match("/.*\\.sk$/"));
		
		$domain->match("/(.*)\\.cz$/",$matches);
		$this->assertEquals("domain",(string)$matches[1]);
	}

	function test_at(){
		$str = new String("Hello");
		$this->assertEquals("H",(string)$str->at(0));
		$this->assertEquals("e",(string)$str->at(1));
		$this->assertEquals("o",(string)$str->at(-1));

		$this->assertEquals("",(string)$str->at(10));
	}

	function test_camelize(){
		foreach(array(
			"hello_world" => "HelloWorld",
			"hello_123" => "Hello123",
			"a_b_c_d" => "ABCD",
		) as $str => $result){
			$str = new String($str);
			$this->assertEquals($result,(string)$str->camelize());
		}
	}

	function test_underscore(){
		foreach(array(
			"HelloWorld" => "hello_world",
			"ABCD" => "abcd",
			"Hello123" => "hello123"
		) as $str => $result){
			$str = new String($str);
			$this->assertEquals($result,(string)$str->underscore());
		}	
	}
}

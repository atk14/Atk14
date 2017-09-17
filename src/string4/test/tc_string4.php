<?php
class TcString4 extends TcBase{
	function test_get_id(){
		$s = new String4("Hi");
		$this->assertTrue(is_string($s->getId()));
		$this->assertEquals("Hi",$s->getId());
	}

	function test_chars(){
		$s = new String4("Hi!");
		$this->assertEquals(array("H","i","!"),$s->chars());
	}

	function test_random_string(){
		$s1 = String4::RandomString();
		$s2 = String4::RandomString();
		$s3 = String4::RandomString(22);

		$this->assertEquals(32,strlen($s1));
		$this->assertEquals(32,strlen($s2));
		$this->assertEquals(22,strlen($s3));

		$this->assertTrue($s1!=$s2);

		$long = String4::RandomString(64);
		$this->assertEquals(64,strlen($long));

		$long = String4::RandomString(1000);
		$this->assertEquals(1000,strlen($long));
	}

	function test_instance(){
		$string = "Hello World";
		$stringer = new String4($string);

		$this->assertTrue(is_object($stringer));
		$this->assertFalse(is_object($string));

		$this->assertEquals("$string","$stringer");
		$this->assertEquals("$string",$stringer->toString());
		$this->assertEquals(11,strlen($stringer));

		// String4::ToObject()
		$string = String4::ToObject("Hello World");
		$this->assertTrue(is_object($string));
		$string2 = String4::ToObject($string); 
		$this->assertTrue(is_object($string2));
	}

	function test_clone_and_copy(){
		$orig = new String4("Hello World","latin1");
		$clone = clone $orig;
		$copy = $orig->copy();

		$orig->replace("Hello","Hi");

		$this->assertEquals("Hi World",(string)$orig);
		$this->assertEquals("Hello World",(string)$clone);
		$this->assertEquals("Hello World",(string)$copy);

		$this->assertEquals("latin1",$orig->getEncoding());
		$this->assertEquals("latin1",$clone->getEncoding());
		$this->assertEquals("latin1",$copy->getEncoding());

	}

	function test_length(){
		$s1 = new String4("pěšinka","utf-8");
		$s2 = new String4("pěšinka","ascii");
		$s3 = new String4("pěšinka"); // default is UTF-8, see initialize

		$this->assertEquals(7,$s1->length());
		$this->assertEquals(9,$s2->length());
		$this->assertEquals(7,$s3->length());
	}

	function test_replace(){
		$str = new String4("Hello World");
		$this->assertEquals("Hello Guys",(string)$str->replace("World","Guys"));

		$str = new String4("Hello World");
		$this->assertEquals("Hi Guys",(string)$str->replace(array(
			"Hello" => "Hi",
			"World" => "Guys",
		)));

		$str = new String4("Hello World");
		$this->assertEquals("Hello World",(string)$str->replace(array()));
	}

	function test_sub(){
		$str = new String4("hello");
		$this->assertEquals("hexxo",(string)$str->gsub("/l/","x"));

		$str = new String4("Hello_World!");
		$this->assertEquals("Hello World!",(string)$str->gsub("/[^A-Z!]/i"," "));
	}

	function test_prepend_and_append(){
		$string = new String4("World");
		$this->assertEquals("Hello World",(string)$string->prepend("Hello "));
		$this->assertEquals("Hello World",(string)$string);

		$string = new String4("Hi");
		$this->assertEquals("Hi World",(string)$string->append(" World"));
		$this->assertEquals("Hi World",(string)$string);
	}

	function test_trim_and_squish(){
		$string = new String4("  Hello\n World \n\r ");
		$this->assertEquals("Hello\n World",(string)$string->trim());
		$this->assertEquals("Hello World",(string)$string->squish());
	}

	function test_match(){
		$domain = new String4("domain.cz");
		$this->assertEquals(true,(bool)$domain->match("/.*\\.cz$/"));
		$this->assertEquals(false,(bool)$domain->match("/.*\\.sk$/"));
		
		$domain->match("/(.*)\\.cz$/",$matches);
		$this->assertEquals("domain",(string)$matches[1]);
	}

	function test_at(){
		$str = new String4("Hello");
		$this->assertEquals("H",(string)$str->at(0));
		$this->assertEquals("e",(string)$str->at(1));
		$this->assertEquals("o",(string)$str->at(-1));
		$this->assertEquals("l",(string)$str->at(-2));

		$this->assertEquals("",(string)$str->at(10));
	}

	function test_first(){
		$str = new String4("hello");
		$this->assertEquals("h",(string)$str->first());
		$this->assertEquals("h",(string)$str->first(1));
		$this->assertEquals("he",(string)$str->first(2));
		$this->assertEquals("hello",(string)$str->first(10));
	}

	function test_contains(){
		$str = new String4("Hello");
		$this->assertTrue($str->contains("ll"));
		$this->assertTrue($str->contains("lo"));
		$this->assertTrue($str->contains("He"));
		$this->assertTrue($str->contains("Hello"));
		$this->assertFalse($str->contains("HELLO"));

		$this->assertFalse($str->contains(new String4("HELLO")));
		$this->assertTrue($str->contains(new String4("Hello")));

		// passing an array...
		// all the elements must be contained when expecting a positive result
		$this->assertTrue($str->contains(array("Hel","llo")));
		$this->assertFalse($str->contains(array("ello","Belle")));

		// containsOneOf
		$this->assertTrue($str->containsOneOf(array("ello","Belle")));
		$this->assertTrue($str->containsOneOf("ello","Belle"));
		$this->assertFalse($str->containsOneOf(array("Nello","Belle")));
		$this->assertFalse($str->containsOneOf("Nello","Belle"));
	}

	function test_camelize(){
		foreach(array(
			"hello_world" => "HelloWorld",
			"hello_123" => "Hello123",
			"a_b_c_d" => "ABCD",
		) as $str => $result){
			$str = new String4($str);
			$this->assertEquals($result,$out = (string)$str->camelize());

			$str = new String4($out);
			$this->assertEquals($result,(string)$str->camelize());
		}

		$str = String4::ToObject("hello_world");
		$this->assertEquals("helloWorld",(string)$str->camelize(array("lower" => true)));
		$this->assertEquals("HelloWorld",(string)$str->camelize());
	}

	function test_underscore(){
		foreach(array(
			"HelloWorld" => "hello_world",
			"ABCD" => "abcd",
			"Hello123" => "hello123",
			"123Hello" => "123_hello",
		) as $str => $result){
			$str = new String4($str);
			$this->assertEquals($result,(string)$str->underscore());
		}	
	}

	function test_tableize(){
		foreach(array(
			"Book" => "books",
			"BlogPost" => "blog_posts",
			"Sheep" => "sheep",
			"Person" => "people",
			"GroupPerson" => "group_people",
		) as $class_name => $table_name){
			$str = new String4($class_name);
			$this->assertEquals($table_name,(string)$str->tableize());
		}	
	}

	function test_pluralize_and_singularize(){
		foreach(array(
			"apple" => "apples",
			"Apple" => "Apples",
			"rotten apple" => "rotten apples",
			"Rotten Apple" => "Rotten Apples",
			"RottenApple" => "RottenApples",
			"rotten_apple" => "rotten_apples",

			"sheep" => "sheep",
			"man" => "men",
			"virus" => "viruses",
			"news" => "news",
		) as $singular => $plural){
			$str = new String4($singular);
			$this->assertEquals($plural,(string)$str->pluralize());

			$str = new String4($plural);
			$this->assertEquals($singular,(string)$str->singularize());
		}
	}

	function test_truncate(){
		$s = new String4("Once upon a time in a world far far away");
		$this->assertEquals("Once upon a time in a wo...",(string)$s->truncate(27));
		$this->assertEquals("Once upon a time in a...",(string)$s->truncate(27, array("separator" => " ")));

		$s = new String4("Once_upon_a_time_in_a_world_far_far_away");
		$this->assertEquals("Once_upon_a_time_in_a_wo...",(string)$s->truncate(27));
		$this->assertEquals("Once_upon_a_time_in_a_wo...",(string)$s->truncate(27, array("separator" => " "))); // pokud v retezci mezera neni, zafunguje to strejne jako v predchozim pripade

		$s = new String4("And they found that many people were sleeping better.");
		$this->assertEquals("And they f... (continued)",(string)$s->truncate(25, array("omission" => "... (continued)")));
		$this->assertEquals("And they... (continued)",(string)$s->truncate(25, array("omission" => "... (continued)", "separator" => " ")));
	}

	function test_upcase_downcase(){
		$s = new String4("Hello");

		$this->assertEquals("HELLO",(string)$s->upcase());
		$this->assertEquals("HELLO",(string)$s->upper());

		$this->assertEquals("hello",(string)$s->downcase());
		$this->assertEquals("hello",(string)$s->lower());

		$s = new String4("Špinavá Ředkvička");
		$this->assertEquals("UTF-8",$s->getEncoding());
		$this->assertEquals("ŠPINAVÁ ŘEDKVIČKA",(string)$s->upcase());
		$this->assertEquals("špinavá ředkvička",(string)$s->lower());
	}

	function test_toAscii(){
		$s = new String4("Špinavá Ředkvička");
		$this->assertEquals("UTF-8",$s->getEncoding());

		$a = $s->toAscii();

		$this->assertEquals("Spinava Redkvicka",(string)$a);
		$this->assertEquals("ASCII",$a->getEncoding());
	}

	function test_toBoolean(){
		foreach(array(
			"off" => false,
			"no" => false,
			"0" => false,
			"n" => false,

			"on" => true,
			"y" => true,
			"yes" => true,
			"1" => true,
		) as $s => $expected){
			$s = new String4($s);
			$this->assertEquals($expected,$s->toBoolean(),"$s");

			$s = new String4(strtoupper($s));
			$this->assertEquals($expected,$s->toBoolean(),"strtoupper($s)");
		}

		$s = new String4(true);
		$this->assertEquals(true,$s->toBoolean());

		$s = new String4(false);
		$this->assertEquals(false,$s->toBoolean());
	}

	function test_substr(){
		$s = new String4("Lorem Ipsum");
		$this->assertEquals("Lorem",(string)$s->substr(0,5));
		$this->assertEquals("Ipsum",(string)$s->substr(-5));
		$this->assertEquals("Lorem Ipsum",(string)$s->substr(0));

		$s = new String4("Špuntíček");
		$this->assertEquals("Š",(string)$s->substr(0,1));
		$this->assertEquals("Špunt",(string)$s->substr(0,5));
		$this->assertEquals("ček",(string)$s->substr(-3));
		$this->assertEquals("Špuntíček",(string)$s->substr(0));
		$this->assertEquals("puntíč",(string)$s->substr(1,6));

		// giving invalid encoding leads to a strange behaviour
		$s = new String4("Špuntíček","latin2");
		$this->assertEquals("Špun",(string)$s->substr(0,5));
	}

	function test_toSlug(){
		$s = new String4("Špinavá Ředkvička!");
		$this->assertEquals("UTF-8",$s->getEncoding());

		$a = $s->toSlug();
		$this->assertEquals("spinava-redkvicka",(string)$a);
		$this->assertEquals("ASCII",$a->getEncoding());

		$this->assertEquals("spinava",(string)$s->toSlug(7));
		$this->assertEquals("spinava",(string)$s->toSlug(8));
		$this->assertEquals("spinava-r",(string)$s->toSlug(9));
	}
}

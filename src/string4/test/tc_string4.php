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

	function test_gsub(){
		$str = new String4("hello");
		$this->assertEquals("hexxo",(string)$str->gsub("/l/","x"));

		$str = new String4("Hello_World!");
		$this->assertEquals("Hello World!",(string)$str->gsub("/[^A-Z!]/i"," "));

		$str = new String4("hello");
		$out = $str->gsub("/^./", function($m) {
			return mb_strtoupper($m[0]);
		});
		$this->assertEquals("Hello", (string)$out);

		$str = new String4("hello");
		$out = $str->gsub("/[l]/", function($m) {
			return "X";
		});
		$this->assertEquals("heXXo", (string)$out);
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
			"hello_World" => "HelloWorld",
			"hello_123" => "Hello123",
			"a_b_c_d" => "ABCD",
			"štika" => "Štika",
			"šišatá_štika" => "ŠišatáŠtika",
			"štika_šišatá" => "ŠtikaŠišatá",
		) as $str => $result){
			$str = new String4($str);
			$this->assertEquals($result,$out = (string)$str->camelize());

			$str = new String4($out);
			$this->assertEquals($result,(string)$str->camelize());
		}

		$str = String4::ToObject("hello_world");
		$this->assertEquals("helloWorld",(string)$str->camelize(array("lower" => true)));
		$this->assertEquals("HelloWorld",(string)$str->camelize());

		$str = String4::ToObject("Štika");
		$this->assertEquals("štika",(string)$str->camelize(array("lower" => true)));

		$str = String4::ToObject("ŠišatáŠtika");
		$this->assertEquals("šišatáŠtika",(string)$str->camelize(array("lower" => true)));

		$str = String4::ToObject("Šišatá štika");
		$this->assertEquals("šišatá štika",(string)$str->camelize(array("lower" => true)));
	}

	function test_underscore(){
		foreach(array(
			"HelloWorld" => "hello_world",
			"ABCD" => "abcd",
			"Hello123" => "hello123",
			"123Hello" => "123_hello",
			"ŠišatáŠtika" => "šišatá_štika",
			"ŠtikaŠišatá" => "štika_šišatá",
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

		// camelize()

		$s = new String4("špinavá ředkvička");
		$this->assertEquals("Špinavá ředkvička",(string)$s->capitalize());
		$this->assertEquals("špinavá ředkvička",(string)$s); // doesn't change the object itself

		$s = new String4("špinavá paní Ředkvička");
		$this->assertEquals("Špinavá paní Ředkvička",(string)$s->capitalize());

		$s = new String4("x");
		$this->assertEquals("X",(string)$s->capitalize());

		$s = new String4("");
		$this->assertEquals("",(string)$s->capitalize());

		// uncapitalize()

		$s = new String4("Nice Try!!!");
		$this->assertEquals("nice Try!!!",(string)$s->uncapitalize());
		$this->assertEquals("Nice Try!!!",(string)$s); // doesn't change the object itself

		$s = new String4("X");
		$this->assertEquals("x",(string)$s->uncapitalize());

		$s = new String4("");
		$this->assertEquals("",(string)$s->uncapitalize());

		// isUpper() & isLower()

		$s = new String4("HELLO!!!");
		$this->assertEquals(true,$s->isUpper());
		$this->assertEquals(false,$s->isLower());

		$s = new String4("hello!!!");
		$this->assertEquals(false,$s->isUpper());
		$this->assertEquals(true,$s->isLower());

		$s = new String4("Hello!!!");
		$this->assertEquals(false,$s->isUpper());
		$this->assertEquals(false,$s->isLower());

		$s = new String4("ŠPINAVÁ ŘEDKVIČKA");
		$this->assertEquals(true,$s->isUpper());
		$this->assertEquals(false,$s->isLower());

		$s = new String4("x");
		$this->assertEquals(false,$s->isUpper());
		$this->assertEquals(true,$s->isLower());

		$s = new String4("!");
		$this->assertEquals(true,$s->isUpper());
		$this->assertEquals(true,$s->isLower());

		$s = new String4("");
		$this->assertEquals(false,$s->isUpper());
		$this->assertEquals(false,$s->isLower());
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
			"" => false,

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

	function test_stripTags_stripHtml(){
		$html = " <html> <!-- Comment? --> <head> <title> TITLE </title> <style> .body{ color: red; } </style> </head> <body>\n <p> <span>Good</span>  Try</p><p>But  &lt;&lt;Wrong&gt;&gt;</p> </body> </html> ";
		$s = new String4($html);

		$this->assertEquals("     TITLE   .body{ color: red; }   \n  Good  TryBut  &lt;&lt;Wrong&gt;&gt;   ",(string)$s->stripTags());
		$this->assertEquals("Good Try But <<Wrong>>",(string)$s->stripHtml());

		$this->assertEquals($html,(string)$s);
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

		// max_length
		$this->assertEquals("spinava",(string)$s->toSlug(array("max_length" => 7)));
		$this->assertEquals("spinava",(string)$s->toSlug(array("max_length" => 8)));
		$this->assertEquals("spinava-r",(string)$s->toSlug(array("max_length" => 9)));
		$this->assertEquals("",(string)$s->toSlug(array("max_length" => 0)));
		$this->assertEquals("",(string)$s->toSlug(array("max_length" => -10)));

		// shortcut for max_length
		$this->assertEquals("spinava",(string)$s->toSlug(7));
		$this->assertEquals("spinava",(string)$s->toSlug(8));
		$this->assertEquals("spinava-r",(string)$s->toSlug(9));

		// suffix
		$this->assertEquals("spinava-redkvicka",(string)$s->toSlug(array("suffix" => "")));
		$this->assertEquals("spinava-redkvicka-chutna",(string)$s->toSlug(array("suffix" => "chutná")));
		$this->assertEquals("spinava-redkvicka",(string)$s->toSlug(array("suffix" => " ")));

		// max_length & suffix combination
		$this->assertEquals("spinava-redkvicka-12345",(string)$s->toSlug(array("max_length" => 100, "suffix" => "12345")));
		$this->assertEquals("spinava-12345",(string)$s->toSlug(array("max_length" => 13, "suffix" => "12345")));
		$this->assertEquals("spin-12345",(string)$s->toSlug(array("max_length" => 10, "suffix" => "12345")));
		$this->assertEquals("s-12345",(string)$s->toSlug(array("max_length" => 7, "suffix" => "12345")));
		$this->assertEquals("12345",(string)$s->toSlug(array("max_length" => 5, "suffix" => "12345")));
		$this->assertEquals("12345",(string)$s->toSlug(array("max_length" => 6, "suffix" => "12345")));

		// suffix has priority over max_length
		$this->assertEquals("12345",(string)$s->toSlug(array("max_length" => 4, "suffix" => "12345")));
		$this->assertEquals("12345",(string)$s->toSlug(array("max_length" => 0, "suffix" => "12345")));
		$this->assertEquals("12345",(string)$s->toSlug(array("max_length" => -10, "suffix" => "12345")));
	}

	function test_fixEncoding(){
		$invalid = chr(200);

		$s = new String4("");
		$this->assertEquals("",$s->fixEncoding());

		$s = new String4("Příliš žluťoučký kůň úpěl ďábelské ódy");
		$this->assertEquals("Příliš žluťoučký kůň úpěl ďábelské ódy",$s->fixEncoding());

		$src = "{$invalid}Příliš{$invalid} žl{$invalid}uťoučký kůň úpěl ďábelské ódy{$invalid}";
		$this->assertFalse(Translate::CheckEncoding($src,"UTF-8"));
		$s = new String4($src);
		$out = (string)$s->fixEncoding();
		$this->assertEquals("�Příliš� žl�uťoučký kůň úpěl ďábelské ódy�",$out);
		$this->assertTrue(Translate::CheckEncoding($out,"UTF-8"));

		$out = (string)$s->fixEncoding(array("replacement" => "?"));
		$this->assertEquals("?Příliš? žl?uťoučký kůň úpěl ďábelské ódy?",$out);
		$this->assertTrue(Translate::CheckEncoding($out,"UTF-8"));

		$out = (string)$s->fixEncoding("▒");
		$this->assertEquals("▒Příliš▒ žl▒uťoučký kůň úpěl ďábelské ódy▒",$out);
		$this->assertTrue(Translate::CheckEncoding($out,"UTF-8"));
	}
}

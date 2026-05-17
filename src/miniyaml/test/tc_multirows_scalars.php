<?php
class TcMultirowsScalars extends TcBase {

	function test_load(){
		$src = '
---
key: |
  line 1
  line 2
		';
		$value = miniYAML::Load($src);
		$this->assertEquals(["key" => "line 1\nline 2"],$value);

		$src = '
---
key: >
  line 1
  line 2
		';
		$value = miniYAML::Load($src);
		$this->assertEquals(["key" => "line 1 line 2"],$value);
	}

	function test_dump(){
		$this->assertEquals(trim('
---
key: |
  line 1
  line 2
		'),trim(miniYAML::Dump([
			"key" => "line 1\nline 2",
		])));
	}

	function test_in_the_middle_of_hash(){
		// Literal block scalar between other keys
		$src = '
---
key1: value1
key2: |
  line 1
  line 2
key3: value3
		';
		$ar = miniYAML::Load($src);
		$this->assertEquals([
			"key1" => "value1",
			"key2" => "line 1\nline 2",
			"key3" => "value3",
		],$ar);

		// Folded block scalar between other keys
		$src = '
---
key1: value1
key2: >
  line 1
  line 2
key3: value3
		';
		$ar = miniYAML::Load($src);
		$this->assertEquals([
			"key1" => "value1",
			"key2" => "line 1 line 2",
			"key3" => "value3",
		],$ar);
	}

	function test_roundtrip(){
		$original = [
			"key1" => "value1",
			"key2" => "line 1\nline 2",
			"key3" => "value3",
		];
		$this->assertEquals($original,miniYAML::Load(miniYAML::Dump($original)));
	}

	function test_dump_trailing_newline(){
		// Strings ending with \n must not produce a trailing line of spaces in the output
		$expected = trim('
---
key: |
  line 1
  line 2
		');

		// Single trailing newline — stripped before dump
		$this->assertEquals($expected,trim(miniYAML::Dump(["key" => "line 1\nline 2\n"])));

		// Multiple trailing newlines — all stripped
		$this->assertEquals($expected,trim(miniYAML::Dump(["key" => "line 1\nline 2\n\n"])));

		// No trailing spaces in the raw output
		$yaml = miniYAML::Dump(["key" => "line 1\nline 2\n"]);
		foreach(explode("\n",$yaml) as $line){
			$this->assertEquals(rtrim($line),$line,"Line contains trailing whitespace: ".json_encode($line));
		}
	}

	function test_empty_block_scalar(){
		// Block scalar with no indented content — value must be "" and subsequent keys must be parsed correctly
		$src = '
---
key1: |
key2: value2
		';
		$ar = miniYAML::Load($src);
		$this->assertTrue(is_array($ar));
		$this->assertEquals(2,count($ar));
		$this->assertEquals("",$ar["key1"]);
		$this->assertEquals("value2",$ar["key2"]);

		// Same for folded
		$src = '
---
key1: >
key2: value2
		';
		$ar = miniYAML::Load($src);
		$this->assertTrue(is_array($ar));
		$this->assertEquals(2,count($ar));
		$this->assertEquals("",$ar["key1"]);
		$this->assertEquals("value2",$ar["key2"]);

		// Block scalar as the last key in the document
		$src = '
---
key1: value1
key2: |
		';
		$ar = miniYAML::Load($src);
		$this->assertTrue(is_array($ar));
		$this->assertEquals(2,count($ar));
		$this->assertEquals("value1",$ar["key1"]);
		$this->assertEquals("",$ar["key2"]);
	}
}

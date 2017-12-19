<?php
class TcFunctions extends TcBase{

	function test_h(){
		$this->assertEquals('&lt;strong&gt;Šupinečka&lt;/strong&gt;',h('<strong>Šupinečka</strong>'));
		$this->assertEquals('&quot;ATK14? Are you sure?&quot;',h('"ATK14? Are you sure?"'));

		$this->assertEquals('hello&#039; dolly',h("hello' dolly"));

		$this->assertEquals('My name is &quot;Nobody&quot;',h('My name is "Nobody"'));
	}

	function test_definedef(){
		$this->assertEquals(false,defined("TESTING_CONSTANT"));
		$this->assertEquals("PrettyContent",definedef("TESTING_CONSTANT","PrettyContent"));
		$this->assertEquals(true,defined("TESTING_CONSTANT"));
		$this->assertEquals("PrettyContent",definedef("TESTING_CONSTANT","ReallyNiceContent"));
	}
}

<?php
class TcFunctions extends TcBase{
	function test_h(){
		$this->assertEquals('&lt;strong&gt;Šupinečka&lt;/strong&gt;',h('<strong>Šupinečka</strong>'));
		$this->assertEquals('&quot;ATK14? Are you sure?&quot;',h('"ATK14? Are you sure?"'));
	}
}

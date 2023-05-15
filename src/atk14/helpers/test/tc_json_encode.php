<?php
class TcJsonEncode extends TcBase {

	function test(){
		$this->assertEquals('{"a":"b","c":"d"}',smarty_modifier_json_encode(["a" => "b", "c" => "d"]));
	}
}

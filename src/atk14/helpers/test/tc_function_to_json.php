<?php
class TcFunctionToJson extends TcBase{
	function test(){
		$smarty = null;
		$this->assertEquals('"hello"',smarty_function_to_json(["var" => "hello"],$smarty));
		$this->assertEquals('{"key":"value"}',smarty_function_to_json(["var" => ["key" => "value"]],$smarty));
		$this->assertEquals('{"key":{"a":"b"}}',smarty_function_to_json(["var" => ["key" => ["a" => "b"]]],$smarty));
	}
}

<?
class tc_function_to_json extends tc_base{
	function test(){
		$smarty = null;
		$this->assertEquals('"hello"',smarty_function_to_json(array("var" => "hello"),$smarty));
		$this->assertEquals('{"key":"value"}',smarty_function_to_json(array("var" => array("key" => "value")),$smarty));
		$this->assertEquals('{"key":{"a":"b"}}',smarty_function_to_json(array("var" => array("key" => array("a" => "b"))),$smarty));
	}
}
?>

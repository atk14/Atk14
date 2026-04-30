<?php
class TcZz01Serialize extends TcBase {

	function test(){
		$tt = TestTable::CreateNewRecord([
			"id" => 1234,
			"create_time" => "2026-04-30 20:20:20",
		]);
		$ser = serialize($tt);

		Files::WriteToFile(__DIR__ ."/tmp/serialized.dat",$ser,$error,$error_str);

		$this->assertEquals(false,$error,$error_str);
	}
}

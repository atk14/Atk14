<?php
class TcScripts extends TcBase {

	function test__dbconsole_command(){
		$cmd = `../../scripts/_dbconsole_command`;
		$this->assertEquals("psql -U 'atk14_demo' 'atk14_demo' -h '127.0.0.1' -p '5432'\n",$cmd);
	}
}

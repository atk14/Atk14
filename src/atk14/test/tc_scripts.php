<?php
class TcScripts extends TcBase {

	function test__dbconsole_command(){
		global $ATK14_GLOBAL;
		$ATK14_GLOBAL = Atk14Global::GetInstance();

		ob_start();
		require("../../scripts/_dbconsole_command");
		$cmd = ob_get_contents();
		ob_end_clean();
		
		$cmd = preg_replace('/#.*?\n/','',$cmd); // "#!/usr/bin/env php" -> ""

		$this->assertEquals("psql -U 'test' 'test' -h '127.0.0.1' -p '5432'\n",$cmd);
	}
}

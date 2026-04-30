<?php
class TcGetDbmole extends TcBase {

	function test(){
		$dbmole = TestTable::GetDbmole();
		$this->assertEquals("default",$dbmole->getConfigurationName());

		$dbmole = TestTableAlternative::GetDbmole();
		$this->assertEquals("alternative",$dbmole->getConfigurationName());

		//
	
		$tt = TestTable::CreateNewRecord([]);
		$tta = TestTableAlternative::CreateNewRecord([]);

		$tt2 = unserialize(serialize($tt));
		$tta2 = unserialize(serialize($tta));

		$this->assertEquals("default",$tt2->dbmole->getConfigurationName());
		$this->assertEquals("alternative",$tta2->dbmole->getConfigurationName());
	}
}

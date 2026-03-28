<?php
class TcNotCollectingStatistics extends TcBase {

	function test(){
		$this->assertEquals(false,DBMOLE_COLLECT_STATISTICS);

		$dbmole = $this->pg;

		$this->assertEquals("Statistical data is not collected",$dbmole->getStatistics());
	}
}

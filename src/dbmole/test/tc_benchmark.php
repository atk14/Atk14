<?php
class tc_benchmark extends tc_base{

	function test(){
		$this->assertTrue(true);
		return;
		// this is an internal test
		$dbmole = $this->ora;
		$counter = 0;
		$limit = 10;
		$sum = 0.0;
		while($counter<$limit){
			$counter++;
			$st = new StopWatch();
			$st->start();
			for($i=0;$i<1000;$i++){
				$dbmole->selectInt("SELECT COUNT(*) FROM test_table");
			}
			$st->stop();
			echo $st->getResult()."\n";
			$sum += $st->getResult();
		}

		echo "---------------\n";
		echo $sum/$limit."\n";
	}
}

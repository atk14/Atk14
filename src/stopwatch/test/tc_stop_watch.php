<?php
class TcStopWatch extends TcBase{

	function test(){
		$sw = new StopWatch();

		$sw->start();
		$sw->start("short");

		usleep(10);
		$sw->stop("short");
		
		usleep(15000);

		$sw->stop();

		$time = $sw->getResult();
		$short_time = $sw->getResult("short");

		$this->assertTrue($time>=0.001,"$time");
		$this->assertTrue($short_time<0.001,"$short_time");
	}

	function test_toString(){
		$sw = new StopWatch();

		$res1 = "$sw";

		usleep(1000);
		$res2 = "$sw";

		$this->assertTrue($res1!=$res2);
	}

	function test_humanize(){
		$sw = new StopWatch();

		$data = array(
			"0.0ms" => 0,
			"1000.0ms" => 1,
			"3522.5ms" => 3.5225,
			"1:00.000" => 60,
			"0:10.000" => 10,
			"1:23.523" => 83.5226
		);

		foreach($data as $exp => $epoch){
			$this->assertEquals($exp,$sw->_humanize($epoch));
		}
	}
}

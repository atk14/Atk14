<?php
class TcStopWatch extends TcBase{
	function test(){
		$sw = new StopWatch();

		$sw->start();
		$sw->start("short");

		usleep(10);
		$sw->stop("short");
		
		usleep(10000);

		$sw->stop();

		$time = $sw->getResult();
		$short_time = $sw->getResult("short");

		$this->assertTrue($time>=0.001);
		$this->assertTrue($short_time<0.001);
	}
}

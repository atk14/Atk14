<?php
class TcTimer extends TcBase {
	function test(){
		$this->assertEquals(null,Atk14Timer::Lap());

		Atk14Timer::Start();

		usleep(1000);

		$lap1 = Atk14Timer::Lap();
		Atk14Timer::Start("subprocess");

		usleep(1000);

		$lap2 = Atk14Timer::Lap();
		$subprocess_lap = Atk14Timer::Lap("subprocess");

		usleep(1000);

		$total = Atk14Timer::Stop();
		$subprocess_total = Atk14Timer::Stop("subprocess");

		$this->assertTrue($total>0.003);
		$this->assertTrue($subprocess_total>0.002);
		$this->assertTrue($total>$subprocess_total);

		$this->assertTrue($lap1>0.001);
		$this->assertTrue($lap2>0.002);
		$this->assertTrue($lap2>$lap1);

		$this->assertTrue($subprocess_lap>0.001);
		$this->assertTrue($lap2>$subprocess_lap);

		$this->assertEquals(null,Atk14Timer::Lap());
		$this->assertEquals(null,Atk14Timer::Lap("subprocess"));
	}
}

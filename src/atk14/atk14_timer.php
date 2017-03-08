<?php
/**
 * Class for measuring time.
 *
 * @filesource
 */

/**
 * Class for measuring time.
 *
 * Can be used to measure code execution.
 * One or more timers can be started, each distinguished by a marker.
 *
 * First start a timer
 * ```
 * Atk14Timer::Start("executing xml parser");
 * ```
 * Then continue with some actions. Each Stop call returns measured time since Start.
 * So you can measure for example total time after each step of a cycle.
 * ```
 * ... some sort of action
 * $time1 = Atk14Timer::Stop("executing xml parser");
 * ... continue with another action
 * $time2 = Atk14Timer::Stop("executing xml parser");
 * var_dump($time1);
 * var_dump($time2);
 * ```
 *
 * @package Atk14\Core
 * @todo Write some explanation
 */
class Atk14Timer {
	/**
	 * Start a timer
	 *
	 * @param string $mark string that distinguishes various timers.
	 */
	static function Start($mark= ""){
		$timer = &Atk14Timer::_GetTimer();
		return $timer->start($mark);
	}

	/**
	 * Stop a timer
	 *
	 * @param string $mark string that distinguishes various timers.
	 * @return float
	 */
	static function Stop($mark = ""){
		$timer = &Atk14Timer::_GetTimer();
		return $timer->stop($mark);	
	}

	/**
	 * Returns a lap time
	 *
	 * Lap time is the time elapsed sice Start method.
	 *
	 * ```
	 * Atk14Timer::Start("runner1");
	 *
	 * sleep(10);
	 * $lap_1 = Atk14Timer::Lap("runner1");
	 * sleep(10);
	 * $lap_2 = Atk14Timer::Lap("runner1");
	 * sleep(10);
	 * $lap_3 = Atk14Timer::Lap("runner1");
	 * printf("first lap: %1.1fs\n", $lap_1); # outputs "first lap 10.0s"
	 * printf("second lap: %1.1fs\n", $lap_2); # outputs "second lap 20.0s"
	 * printf("third lap: %1.1fs\n", $lap_3); # outputs "third lap 30.0s"
	 * ```
	 * @param string $mark timer identification
	 * @return float
	 */
	static function Lap($mark = ""){
		$timer = &Atk14Timer::_GetTimer();
		return $timer->lap($mark);
	}

	/**
	 * Outputs list of all timers with measured times,
	 *
	 * @param array $options
	 * - total_results_only - default true - when false the output is more detailed
	 * @return string
	 * @todo some explanation regarding counters could be helpful
	 */
	static function GetResult($options = array()){
		$options = array_merge(array(
			"total_results_only" => true
		),$options);

		$timer = &Atk14Timer::_GetTimer();
		return $timer->getPrintableOutput($options);
	}

	/**
	 * Get instance of the measuring object.
	 *
	 * @return StopWatch
	 */
	private static function &_GetTimer(){
		static $timer;

		if(!isset($timer)){ $timer = new StopWatch(); }
		return $timer;
	}
}

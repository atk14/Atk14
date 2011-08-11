<?php
/**
 * Class for measuring time.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Class for measuring time.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @todo Write some explanation
 */
class Atk14Timer {
	/**
	 * @static
	 */
	function Start($mark= ""){
		$timer = &Atk14Timer::_GetTimer();
		return $timer->start($mark);
	}

	/**
	 * @static
	 */
	function Stop($mark = ""){
		$timer = &Atk14Timer::_GetTimer();
		return $timer->stop($mark);	
	}

	function GetResult($options = array()){
		$options = array_merge(array(
			"total_results_only" => true
		),$options);

		$timer = &Atk14Timer::_GetTimer();
		return $timer->getPrintableOutput($options);
	}

	/**
	 * @static
	 * @access private
	 */
	function &_GetTimer(){
		static $timer;

		if(!isset($timer)){ $timer = new StopWatch(); }
		return $timer;
	}
}
?>

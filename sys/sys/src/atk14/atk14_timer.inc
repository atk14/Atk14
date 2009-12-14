<?
class Atk14Timer {
	function Start($mark= ""){
		$timer = &Atk14Timer::_GetTimer();
		return $timer->start($mark);
	}

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

	function &_GetTimer(){
		static $timer;

		if(!isset($timer)){ $timer = new StopWatch(); }
		return $timer;
	}
}
?>

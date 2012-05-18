<?php
/**
* $s = new StopWatch();
*
* $s->start();
*	// do something 
* echo $s->stop();
*
* $s->start("rendering");
* // do something 
* echo $s->stop("rendering");
*
* echo $s->getPrintableOutput();
*/
class StopWatch{

	var $_ResultsStore = array();

	function StopWatch(){
		$this->start("_auto_start_");
	}

	/**
	*	Vrati unixstamp vc. desetinne casti.
	*
	* @access private
	* @return float
	*/
	function _getMicroTime(){
    $time = microtime();
		$pieces = explode(" ",$time);
    return (float)$pieces[1]+(float)$pieces[0];
	}

	/**
	* Spusti stopky pro danou znacku
	* 
	* @access public
	* @param string $mark				jmeno znacky
	*/
	function start($mark = ""){
		settype($mark,"string");
		$this->_ResultsStore[] = array(
			"mark" => $mark,
			"start" => $this->_getMicroTime(),
			"stop" => null
		);
	}

	/**
	* Zastavi stopky pro danou znacku
	* 
	* @access public
	* @param string $mark				jmeno znacky
	*/
	function stop($mark = ""){
		settype($mark,"string");
		$_stop = $this->_getMicroTime();
		for($i=sizeof($this->_ResultsStore)-1;$i>=0;$i--){
			if($this->_ResultsStore[$i]["mark"]==$mark){
				$this->_ResultsStore[$i]["stop"] = $_stop;
				break;
			}
		}
		return $this->getResult($mark);
	}

	/**
	* 
	* @access public
	* @param string $mark					jmeno znacky
	* @return float								nebo null, v pripade, ze cas nelze urcit
	*/
	function getResult($mark = ""){
		settype($mark,"string");
		$out = null;
		for($i=sizeof($this->_ResultsStore)-1;$i>=0;$i--){
			if($this->_ResultsStore[$i]["mark"]==$mark){
				if(isset($this->_ResultsStore[$i]["start"])){
					$stop = isset($this->_ResultsStore[$i]["stop"]) ? $this->_ResultsStore[$i]["stop"] : $this->_getMicroTime();
					$out = $stop - $this->_ResultsStore[$i]["start"];
				}
				break;
			}
		}
		return $out;
	}

	function result($mark = ""){ return $this->getResult($mark); }

	/**
	* Vytvori sestavu se vsemi vysledky.
	*
	* @access public
	* @return string
	*/
	function getPrintableOutput($options = array()){
		$options = array_merge(array(
			"total_results_only" => false
		),$options);

		if(sizeof($this->_ResultsStore) == 0){
			return "nothing has been measured";
		}
		$out = array();
		$totals = array();
		for($i=0;$i<sizeof($this->_ResultsStore);$i++){
			$mark = $this->_ResultsStore[$i]["mark"];
			$result = null;
			if(isset($this->_ResultsStore[$i]["start"]) && isset($this->_ResultsStore[$i]["stop"])){
				$result = $this->_ResultsStore[$i]["stop"] - $this->_ResultsStore[$i]["start"];
			}

			if(!isset($totals["$mark"])){ $totals["$mark"] = array("time" => 0.0, "counter" => 0); }
			$totals["$mark"]["time"] += (float)$result;
			$totals["$mark"]["counter"]++;

			if($options["total_results_only"]){ continue; }

			if(isset($result)){
				$out[] = sprintf("%30s: %9s",$mark,number_format($result,6,".",""));
			}else{
				$out[] = sprintf("%30s: %9s",$mark,"null");
			}
		}

		$out[] = sprintf("%30s: %s","----------------","total");
		foreach($totals as $mark => $result){
				$out[] = sprintf("%30s x %d: %9ss",$mark,$result["counter"],number_format($result["time"],4,".",""));
		}
		return join("\n",$out);
	}

	/**
	 * Returns result in a human readable format.
	 *
	 * <code>
	 * $sw = new StopWatch();
	 * $sw->start("main");
	 * // do something
	 * $sw->stop("main");
	 * echo $sw->toString("main");
	 *
	 * // or
	 *
	 * $sw = new StopWatch();
	 * // do something
	 * echo "$sw"; // see StopWatch::__toString()
	 * </code>
	 *
	 */
	function toString($mark = "_auto_start_"){
		return $this->_humanize($this->getResult($mark));
	}

	function __toString(){
		return $this->toString();
	}

	function _humanize($seconds){
		$minutes = floor($seconds/60);
		$seconds = $seconds - (60 * $minutes);
		$seconds = number_format($seconds,3,".","");
		if($seconds<10){
			$seconds = "0$seconds";
		}
		
		return "$minutes:$seconds";
	}
}

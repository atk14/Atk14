<?
/**
* $d = new Date("2008-01-01"); // v pripade, ze bude zadan naplatny datum, muze dojit k nepredvidatelnym vecem!!!
* $d = Date::ByDate("2008-01-01"); // preferovany zpusob; vrati null, pokud nebude zadan validni datum
*
* $today = Date::Today();
* $yesterday = Date::Yesterday();
* $tomorrow = Date::Tomorrow();
*
* $d->getUnixTimestamp();
* $d->toString();
*
* // nasledujici meni primo instanci
* $d->addDay();
* $d->addDays(10);
* $d->addDays(-2);
*
* // nasledujici pracuje nad kopiema instance
* $d1 = $d->plusDay();
* $d2 = $d->plusDays(10);
* $d3 = $d->minusDay();
* $d4 = $d->minusDays(10);
*
* if($d->isMonday()){
*		// mame pondeli
*	}
*
* $next_friday = $d->getNextFriday(); // vrati nesledujici patek vzhledem k tomuto dni; vzdy vrati den v budounosti; tj. muze vratit den v akt. tydnu
* $current_friday = $d->getCurrentWeekFriday(); // vrati patek v akt. tydnu; muze vratit den v minulosti, v budoucnosti nebo stejny den
* $next_week_friday = $d->getNextWeekFriday();
* $prev_week_friday = $d->getPrevWeekFriday();
*/
class Date{
	function Date($date){
		if(!dates::check_date($date)){ $date = null; }
		$this->_Date = $date;
	}
	function copy(){ return new Date($this->getDate()); }

	function Today(){ $out = new Date(dates::now()); return $out; }
	function Yesterday(){ $out = Date::Today(); return $out->minusDay(); }
	function Tomorrow(){ $out = Date::Today(); return $out->plusDay(); }

	/**
	* $date = Date::ByDate("2001-01-31");
	* $date = Date::ByDate(array("year" => 2001,"month" => 1, "day" => 31));
	* $date = Date::ByDate("2001-01-31 12:30:00"); // cas bude automaticky odseknut
	*/
	function ByDate($date){
		if(is_array($date)){
			$date = sprintf("%d-%02d-%02d",$date["year"],$date["month"],$date["day"]);
		}
		$date = substr($date,0,10); // odseknuti casu
		$out = new Date("$date"); if(!$out->getDate()){ return null; }
		return $out;
	}	
	
	function getDate(){ return $this->_Date; }
	function toString($format = "Y-m-d"){ return date($format,$this->getUnixTimestamp()); }

	function format($format_string){ return date($format_string,$this->getUnixTimestamp()); }

	function getUnixTimestamp(){
		return (int)strtotime($this->getDate());
	}
	
	function addDay(){ $this->addDays(1); }
	function addDays($days){ $this->_Date = dates::add_days($this->getDate(),$days); }
	function remDay(){ $this->addDays(-1); }
	function remDays($days){ $this->addDays(-$days); }
	function plusDay(){ return $this->plusDays(1); }
	function minusDay(){ return $this->plusDays(-1); }
	function plusDays($days){ $out = $this->copy(); $out->addDays($days); return $out; }
	function minusDays($days){ $out = $this->copy(); $out->addDays(-$days); return $out; }
	function nextDay(){ return $this->plusDay(); }
	function prevDay(){ return $this->minusDay(); }

	function addYear(){ $this->addYears(1); }
	function addYears($years){ $this->_Date = dates::add_years($this->getDate(),$years);	}
	function remYear(){ $this->addYears(-1); }
	function remYears($years){ $this->addYears(-$years); }
	function plusYear(){ return $this->plusYears(1); }
	function minusYear(){ return $this->plusYears(-1); }
	function plusYears($years){ $out = $this->copy(); $out->addYears($years); return $out; }
	function minusYears($years){ $out = $this->copy(); $out->addYears(-$years); return $out; }
	function nextYear(){ return $this->plusYear(); }
	function prevYear(){ return $this->minusYear(); }

	function addMonth(){ $this->addMonths(1); }
	function addMonths($months){ $this->_Date = dates::add_months($this->getDate(),$months);	}
	function remMonth(){ $this->addMonths(-1); }
	function remMonths($months){ $this->addMonths(-$months); }
	function plusMonth(){ return $this->plusMonths(1); }
	function minusMonth(){ return $this->plusMonths(-1); }
	function plusMonths($months){ $out = $this->copy(); $out->addMonths($months); return $out; }
	function minusMonths($months){ $out = $this->copy(); $out->addMonths(-$months); return $out; }
	function nextMonth(){ return $this->plusMonth(); }
	function prevMonth(){ return $this->minusMonth(); }

	function addWeek(){ $this->addWeeks(1); }
	function addWeeks($weeks){ $this->addDays($weeks * 7);	}
	function remWeek(){ $this->addWeeks(-1); }
	function remWeeks($weeks){ $this->addWeeks(-$weeks); }
	function plusWeek(){ return $this->plusWeeks(1); }
	function minusWeek(){ return $this->plusWeeks(-1); }
	function plusWeeks($weeks){ $out = $this->copy(); $out->addWeeks($weeks); return $out; }
	function minusWeeks($weeks){ $out = $this->copy(); $out->addWeeks(-$weeks); return $out; }
	function nextWeek(){ return $this->plusWeek(); }
	function prevWeek(){ return $this->minusWeek(); }

	/**
	* $d = Date::Today();
	* $d->daysFromToday(); // 0
	*
	* $d = Date::Yesterday();
	* $d->daysFromToday(); // -1
	*
	* $d = Date::Tomorrow();
	* $d->daysFromToday(); // +1
	*/
	function daysFromToday(){ return dates::get_difference(dates::now(),$this->getDate()); }
	function isWeekend(){ return ($this->getIsoWeekDay()==6 || $this->getIsoWeekDay()==7); }
	function isToday(){ return $this->compare(Date::Today())==0; }
	function isTomorow(){ return $this->compare(Date::Tomorrow())==0; }
	function isYesterday(){ return $this->compare(Date::Yesterday())==0;  }
	function isFuture(){ return $this->compare(Date::Today())>0; }
	function isPast(){ return $this->compare(Date::Today())<0; }

	function isMonday(){ return $this->getIsoWeekDay()==1; }
	function isTuesday(){ return $this->getIsoWeekDay()==2; }
	function isWednesday(){ return $this->getIsoWeekDay()==3; }
	function isThursday(){ return $this->getIsoWeekDay()==4; }
	function isFriday(){ return $this->getIsoWeekDay()==5; }
	function isSaturday(){ return $this->getIsoWeekDay()==6; }
	function isSunday(){ return $this->getIsoWeekDay()==7; }

	/**
	* $d1 = Date::Today();
	* $d2 = Date::Today();
	*                                            
	* $this->assertEquals(0,$d1->compare($d2));
	*                                            
	* $d2 = Date::Tomorrow();
	* $this->assertEquals(-1,$d1->compare($d2));
	*                                            
	* $d2 = Date::Yesterday();
	* $this->assertEquals(1,$d1->compare($d2));
	*/
	function compare($date){
		$date = $this->_toString($date);
		return dates::compare($this->getDate(),$date);
	}

	function isTheSame($date){ return $this->compare($date)==0; }
	function isOlderThan($date){ return $this->compare($date)<0; }
	function isOlderOrSameLike($date){ return $this->isOlderThan($date) || $this->isTheSame($date); }
	function isNewerThan($date){ return $this->compare($date)>0; }
	function isNewerOrSameLike($date){ return $this->isNewerThan($date) || $this->isTheSame($date); }

	/**
	* 0 .. Sunday
	* 6 .. Saturday
	*/
	function getWeekDay(){
		return (int)date("w",$this->getUnixTimestamp());
	}
	/**
	* ISO-8601 numeric representation of the day of the week
	* 1 .. Monday
	* 7 .. Sunday
	*/
	function getIsoWeekDay(){
		$out = $this->getWeekDay();
		if($out==0){ $out = 7; }
		return $out;
	}

	function getNextMonday(){ 		return $this->_getNextDay(1);	}
	function getNextTuesday(){ 		return $this->_getNextDay(2);	}
	function getNextWednesday(){ 	return $this->_getNextDay(3);	}
	function getNextThursday(){ 	return $this->_getNextDay(4);	}
	function getNextFriday(){ 		return $this->_getNextDay(5);	}
	function getNextSaturday(){ 	return $this->_getNextDay(6); }
	function getNextSunday(){ 		return $this->_getNextDay(7);	}

	function getCurrentWeekMonday(){ 		return $this->_getCurrentWeekDay(1);	}
	function getCurrentWeekTuesday(){ 	return $this->_getCurrentWeekDay(2);	}
	function getCurrentWeekWednesday(){ return $this->_getCurrentWeekDay(3);	}
	function getCurrentWeekThursday(){ 	return $this->_getCurrentWeekDay(4);	}
	function getCurrentWeekFriday(){ 		return $this->_getCurrentWeekDay(5);	}
	function getCurrentWeekSaturday(){ 	return $this->_getCurrentWeekDay(6); }
	function getCurrentWeekSunday(){ 		return $this->_getCurrentWeekDay(7);	}

	function getNextWeekMonday(){ 		return $this->_getNextWeekDay(1);	}
	function getNextWeekTuesday(){ 	return $this->_getNextWeekDay(2);	}
	function getNextWeekWednesday(){ return $this->_getNextWeekDay(3);	}
	function getNextWeekThursday(){ 	return $this->_getNextWeekDay(4);	}
	function getNextWeekFriday(){ 		return $this->_getNextWeekDay(5);	}
	function getNextWeekSaturday(){ 	return $this->_getNextWeekDay(6); }
	function getNextWeekSunday(){ 		return $this->_getNextWeekDay(7);	}

	function getPrevWeekMonday(){ 		return $this->_getPrevWeekDay(1);	}
	function getPrevWeekTuesday(){ 	return $this->_getPrevWeekDay(2);	}
	function getPrevWeekWednesday(){ return $this->_getPrevWeekDay(3);	}
	function getPrevWeekThursday(){ 	return $this->_getPrevWeekDay(4);	}
	function getPrevWeekFriday(){ 		return $this->_getPrevWeekDay(5);	}
	function getPrevWeekSaturday(){ 	return $this->_getPrevWeekDay(6); }
	function getPrevWeekSunday(){ 		return $this->_getPrevWeekDay(7);	}

	function getMonthFirstDay(){ return new Date(dates::get_first_date_by_date($this->getDate())); }
	function getMonthLastDay(){ return new Date(dates::get_last_date_by_date($this->getDate())); }

	function getPrevMonthFirstDay(){
		$out = $this->getMonthFirstDay();
		$out->addDays(-1);
		return $out->getMonthFirstDay();
	}

	function getNextMonthFirstDay(){
		$out = $this->getMonthLastDay();
		return $out->plusDay();
	}

	function _getNextDay($iso_week_day){
		$out = $this->plusDay();
		while($out->getIsoWeekDay()!=$iso_week_day){ $out = $out->plusDay(); }
		return $out;
	}

	function _getCurrentWeekDay($iso_week_day){
		$out = $this->copy();
		if($out->getIsoWeekDay()==$iso_week_day){ return $out; }
		if($iso_week_day<$out->getIsoWeekDay()){
			$out->addDays(-7);
		}
		return $out->_getNextDay($iso_week_day);
	}

	function _getNextWeekDay($iso_week_day){
		$out = $this->copy();
		$out->addDays(7);
		return $out->_getCurrentWeekDay($iso_week_day);
	}

	function _getPrevWeekDay($iso_week_day){
		$out = $this->copy();
		$out->addDays(-7);
		return $out->_getCurrentWeekDay($iso_week_day);
	}

	function _toString($date){
		if(!is_string($date)){ $date = $date->getDate(); }
		return $date;
	}
}
?>

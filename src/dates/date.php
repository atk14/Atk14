<?php
/**
 * Class for performing operations on dates.
 *
 * @package Atk14
 * @subpackage Date
 * @filesource
 */
/**
 * Class for performing operations on dates.
 *
 * Basic usage
 * ```
 * $d = new Date("2008-01-01");
 * ```
 * Unexpected thing may happen when input date is not valid so the following usage is preferred.
 *
 * This call returns null when input date is not valid.
 * ```
 * $d = Date::ByDate("2008-01-01");
 * ```
 *
 * Getting yesterday, today a tommorow date
 * ```
 * $today = Date::Today();
 * $yesterday = Date::Yesterday();
 * $tomorrow = Date::Tomorrow();
 * ```
 *
 * Getting various output formats.
 * ```
 * $d = Date::Today();
 * $d->getUnixTimestamp();
 * $d->toString();
 * ```
 *
 * Following methods change the instance value
 * ```
 * $d->addDay();
 * $d->addDays(10);
 * $d->addDays(-2);
 * ```
 *
 * Following methods return copy of an instance
 * ```
 * $d1 = $d->plusDay();
 * $d2 = $d->plusDays(10);
 * $d3 = $d->minusDay();
 * $d4 = $d->minusDays(10);
 * ```
 *
 * Check if the date is monday
 * ```
 * if($d->isMonday()){
 * 	ok, it's monday
 * }
 * ```
 *
 * @package Atk14
 * @subpackage Date
 * @filesource
 */
class Date{
	/**
	 * Constructor
	 *
	 * When input date is invalid, results using the object are unpredictable.
	 * So recommended initialization is using method ByDate().
	 *
	 * ```
	 * $d = new Date("2008-01-01");
	 * ```
	 *
	 * @param string $date
	 */
	function __construct($date){
		if(!Dates::CheckDate($date)){ $date = null; }
		$this->_Date = $date;
	}
	/**
	 * Returns copy of the instance.
	 *
	 * @return Date
	 */
	function copy(){ return new Date($this->getDate()); }

	/**
	 * Returns object with current date.
	 *
	 * @return Date
	 */
	static function Today(){ $out = new Date(Dates::Now()); return $out; }

	/**
	 * Returns object with yesterday's date.
	 *
	 * @return Date
	 */
	static function Yesterday(){ $out = Date::Today(); return $out->minusDay(); }
	
	/**
	 * Returns object with tomorrow's date.
	 *
	 * @return Date
	 */
	static function Tomorrow(){ $out = Date::Today(); return $out->plusDay(); }

	/**
	 * Date object initialization.
	 *
	 * This method is preferred to using constructor.
	 *
	 * Date given as string
	 * ```
	 *	$date = Date::ByDate("2001-01-31");
	 * ```
	 *
	 * Input can be given as array with 'year','month','day' fields.
	 * ```
	 *	$date = Date::ByDate(array("year" => 2001,"month" => 1, "day" => 31));
	 * ```
	 *
	 * time information is cut off.
	 * ```
	 *	$date = Date::ByDate("2001-01-31 12:30:00");
	 * ```
	 *
	 * @param string|array $date
	 */
	static function ByDate($date){
		if(is_array($date)){
			$date = sprintf("%d-%02d-%02d",$date["year"],$date["month"],$date["day"]);
		}
		$date = substr($date,0,10); // odseknuti casu
		$out = new Date("$date"); if(!$out->getDate()){ return null; }
		return $out;
	}	
	
	/**
	 * Returns date as ISO formatted string.
	 *
	 * @return string
	 */
	function getDate(){ return $this->_Date; }

	/**
	 * Returns date as ISO formatted string.
	 *
	 * @param string $format
	 * @return string
	 */
	function toString($format = "Y-m-d"){ return date($format,$this->getUnixTimestamp()); }

	/**
	 * Alias to method {@link toString()}
	 *
	 * @return string
	 */
	function getId(){ return $this->toString(); }

	/**
	 * Returns date formatted as GMT string.
	 *
	 * @param string $format
	 * @return string
	 */
	function toGmdate($format = 'D, d M Y H:i:s \G\M\T'){ return gmdate($format,$this->getUnixTimestamp()); }

	/**
	 * Returns formatted string representation of date.
	 *
	 * @param string $format_string
	 * @return string
	 */
	function format($format_string){ return date($format_string,$this->getUnixTimestamp()); }

	/**
	 * Returns objects date as unix timestamp.
	 *
	 * @return integer
	 */
	function getUnixTimestamp(){
		return (int)strtotime($this->getDate());
	}
	
	/**
	 * Adds one day to the date contained in object.
	 *
	 * Changes the instance directly.
	 *
	 */
	function addDay(){ $this->addDays(1); }

	/**
	 * Adds days to the date contained in object.
	 *
	 * Changes the instance directly.
	 *
	 * @param integer $days number of days to add
	 */
	function addDays($days){ $this->_Date = Dates::AddDays($this->getDate(),$days); }

	/**
	 * Subtracts one day from the date contained in the object.
	 *
	 * Changes the instance directly.
	 *
	 */
	function remDay(){ $this->addDays(-1); }

	/**
	 * Subtracts days from the date contained in the object.
	 *
	 * Changes the instance directly.
	 *
	 * @param integer $days number of days to subtract
	 */
	function remDays($days){ $this->addDays(-$days); }

	/**
	 * Returns Date increased by one day.
	 *
	 * @return Date
	 */
	function plusDay(){ return $this->plusDays(1); }

	/**
	 * Returns Date decreased by one day.
	 *
	 * @return Date
	 */
	function minusDay(){ return $this->plusDays(-1); }

	/**
	 * Returns Date increased by days.
	 *
	 * @param integer $days
	 * @return Date
	 */
	function plusDays($days){ $out = $this->copy(); $out->addDays($days); return $out; }

	/**
	 * Returns Date decreased by days.
	 *
	 * @param integer $days
	 * @return Date
	 */
	function minusDays($days){ $out = $this->copy(); $out->addDays(-$days); return $out; }

	/**
	 * Alias to method plusDay().
	 *
	 * @return Date
	 */
	function nextDay(){ return $this->plusDay(); }

	/**
	 * Returns date set to one day back.
	 * Alias to method minusDay().
	 *
	 * @return Date
	 */
	function prevDay(){ return $this->minusDay(); }

	/**
	 * Sets date to one year ahead.
	 *
	 * Changes the instance directly.
	 */
	function addYear(){ $this->addYears(1); }

	/**
	 * Sets date to years ahead.
	 *
	 * Changes the instance directly.
	 *
	 * @param integer $years
	 */
	function addYears($years){ $this->_Date = Dates::AddYears($this->getDate(),$years);	}

	/**
	 * Sets date to one year ahead.
	 *
	 * Changes the instance directly.
	 *
	 */
	function remYear(){ $this->addYears(-1); }

	/**
	 * Sets date to years ahead.
	 *
	 * Changes the instance directly.
	 *
	 * @param integer $years
	 */
	function remYears($years){ $this->addYears(-$years); }

	/**
	 * Returns date set to one year ahead.
	 *
	 * @return Date
	 */
	function plusYear(){ return $this->plusYears(1); }

	/**
	 * Returns date set to one year back.
	 *
	 * @return Date
	 */
	function minusYear(){ return $this->plusYears(-1); }

	/**
	 * Returns date set to years ahead.
	 *
	 * @param int $years
	 * @return Date
	 */
	function plusYears($years){ $out = $this->copy(); $out->addYears($years); return $out; }
	/**
	 * Returns date set to years back.
	 *
	 * @param int $years
	 * @return Date
	 */
	function minusYears($years){ $out = $this->copy(); $out->addYears(-$years); return $out; }

	/**
	 * Returns date set to one year ahead.
	 *
	 * Alias to method {@link plusYear()}
	 *
	 * @return Date
	 */
	function nextYear(){ return $this->plusYear(); }

	/**
	 * Returns date set to one year back.
	 *
	 * Alias to method {@link minusYear()}
	 *
	 * @return Date
	 */
	function prevYear(){ return $this->minusYear(); }

	/**
	 * Sets date to one month ahead.
	 *
	 * Changes the instance directly.
	 */
	function addMonth(){ $this->addMonths(1); }

	/**
	 * Sets date to months ahead.
	 *
	 * Changes the instance directly.
	 *
	 * @param integer $months
	 */
	function addMonths($months){ $this->_Date = Dates::AddMonths($this->getDate(),$months);	}

	/**
	 * Sets date to one month back.
	 *
	 * Changes the instance directly.
	 */
	function remMonth(){ $this->addMonths(-1); }

	/**
	 * Sets date to months ahead.
	 *
	 * Changes the instance directly.
	 *
	 * @param integer $months
	 */
	function remMonths($months){ $this->addMonths(-$months); }

	/**
	 * Returns date set to one month ahead.
	 *
	 * @return Date
	 */
	function plusMonth(){ return $this->plusMonths(1); }

	/**
	 * Returns date set to one month back.
	 *
	 * @return Date
	 */
	function minusMonth(){ return $this->plusMonths(-1); }

	/**
	 * Returns date set to months ahead.
	 *
	 * @param integer $months
	 * @return Date
	 */
	function plusMonths($months){ $out = $this->copy(); $out->addMonths($months); return $out; }

	/**
	 * Returns date set to months back.
	 *
	 * @param integer $months
	 * @return Date
	 */
	function minusMonths($months){ $out = $this->copy(); $out->addMonths(-$months); return $out; }

	/**
	 * Returns date set to one month back.
	 *
	 * Alias to method {@link plusMonth()}
	 *
	 * @return Date
	 */
	function nextMonth(){ return $this->plusMonth(); }

	/**
	 * Returns date set to one month back.
	 * Alias to method {@link minusMonth()}
	 *
	 * @return Date
	 */
	function prevMonth(){ return $this->minusMonth(); }

	/**
	 * Sets date to one week ahead.
	 *
	 */
	function addWeek(){ $this->addWeeks(1); }

	/**
	 * Sets date to weeks ahead.
	 *
	 * Changes the instance directly.
	 *
	 * @param integer $weeks
	 */
	function addWeeks($weeks){ $this->addDays($weeks * 7);	}

	/**
	 * Sets date to one week back.
	 *
	 * Changes the instance directly.
	 */
	function remWeek(){ $this->addWeeks(-1); }

	/**
	 * Sets date to week back
	 *
	 * Changes the instance directly.
	 *
	 * @param integer $weeks
	 */
	function remWeeks($weeks){ $this->addWeeks(-$weeks); }

	/**
	 * Return date set to one week ahead.
	 *
	 * @return Date
	 */
	function plusWeek(){ return $this->plusWeeks(1); }

	/**
	 * Returns date set to one week back.
	 *
	 * @return Date
	 */
	function minusWeek(){ return $this->plusWeeks(-1); }

	/**
	 * Returns date set to weeks ahead.
	 *
	 * @param integer $weeks
	 * @return Date
	 */
	function plusWeeks($weeks){ $out = $this->copy(); $out->addWeeks($weeks); return $out; }

	/**
	 * Returns date set to weeks back.
	 *
	 * @param integer $weeks
	 * @return Date
	 */
	function minusWeeks($weeks){ $out = $this->copy(); $out->addWeeks(-$weeks); return $out; }

	/**
	 * Returns date set to one week ahead.
	 *
	 * Alias to method {@link plusWeek()}
	 *
	 * @return Date
	 */
	function nextWeek(){ return $this->plusWeek(); }
	/**
	 * Returns date set to one week back.
	 *
	 * Alias to method {@link minusWeek()}
	 *
	 * @return Date
	 */
	function prevWeek(){ return $this->minusWeek(); }

	/**
	 * Number of days from today.
	 *
	 * ```
	 * $d = Date::Today();
	 * $d->daysFromToday(); // 0
	 *
	 * $d = Date::Yesterday();
	 * $d->daysFromToday(); // -1
	 *
	 * $d = Date::Tomorrow();
	 * $d->daysFromToday(); // +1
	 * ```
	 *
	 * @return integer
	 */
	function daysFromToday(){ return $this->daysFrom(Date::Today()); }

	/**
	 * Number of days from given date
	 *
	 * @param Date $date
	 * @return integer
	 */
	function daysFrom($date){
		if(!is_object($date)){
			$date = Date::ByDate($date);
		}
		return Dates::GetDifference($date->getDate(),$this->getDate());
	}

	/**
	 * Checks if objects date is on weekend.
	 *
	 * @return bool
	 */
	function isWeekend(){ return ($this->getIsoWeekDay()==6 || $this->getIsoWeekDay()==7); }

	/**
	 * Checks if date in object is today.
	 *
	 * @return bool
	 */
	function isToday(){ return $this->compare(Date::Today())==0; }

	/**
	 * Checks if date in object is tomorrow.
	 *
	 * @return bool
	 */
	function isTomorow(){ return $this->compare(Date::Tomorrow())==0; }

	/**
	 * Checks if date in object is yesterday.
	 *
	 * @return bool
	 */
	function isYesterday(){ return $this->compare(Date::Yesterday())==0;  }

	/**
	 * Checks if date in object is future.
	 *
	 * @return bool
	 */
	function isFuture(){ return $this->compare(Date::Today())>0; }

	/**
	 * Checks if date in object is in past.
	 *
	 * @return bool
	 */
	function isPast(){ return $this->compare(Date::Today())<0; }

	/**
	 * Checks if date in object is monday.
	 *
	 * @return bool
	 */
	function isMonday(){ return $this->getIsoWeekDay()==1; }

	/**
	 * Checks if date in object is tuesday.
	 *
	 * @return bool
	 */
	function isTuesday(){ return $this->getIsoWeekDay()==2; }

	/**
	 * Checks if date in object is wednesday.
	 *
	 * @return bool
	 */
	function isWednesday(){ return $this->getIsoWeekDay()==3; }
	
	/**
	 * Checks if date in object is thursday.
	 *
	 * @return bool
	 */
	function isThursday(){ return $this->getIsoWeekDay()==4; }
	
	/**
	 * Checks if date in object is in friday.
	 *
	 * @return bool
	 */
	function isFriday(){ return $this->getIsoWeekDay()==5; }
	
	/**
	 * Checks if date in object is saturday.
	 *
	 * @return bool
	 */
	function isSaturday(){ return $this->getIsoWeekDay()==6; }

	/**
	 * Checks if date in object is in sunday.
	 *
	 * @return bool
	 */
	function isSunday(){ return $this->getIsoWeekDay()==7; }

	/**
	 * Compares date with another.
	 *
	 * On invalid input date the method returns false.
	 *
	 * ```
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
	 * ```
	 *
	 * @param Date $date Date to compare with
	 * @return integer 0 when compared date is equal, -1 when compared date is newer, 1 when compared date is older
	 */
	function compare($date){
		$date = $this->_toString($date);
		return Dates::Compare($this->getDate(),$date);
	}

	/**
	 * Compares date with another and checks if it is the same as this instance.
	 *
	 * @param Date $date Compared date.
	 * @return bool
	 */
	function isTheSame($date){ return $this->compare($date)==0; }

	/**
	 * Alias for method isTheSame
	 *
	 * @param Date $date Compared date.
	 * @return bool
	 */
	function isSameLike($date){ return $this->isTheSame($date); } // lepsi english :)

	/**
	 * Checks if the objects date is older than another date.
	 *
	 * @param Date $date
	 * @return bool
	 */
	function isOlderThan($date){ return $this->compare($date)<0; }

	/**
	 * Checks if the objects date is the same as or older than another date.
	 *
	 * @param Date $date
	 * @return bool
	 */
	function isOlderOrSameLike($date){ return $this->isOlderThan($date) || $this->isTheSame($date); }

	/**
	 * Checks if the objects date is newer than another date.
	 *
	 * @param Date $date
	 * @return bool
	 */
	function isNewerThan($date){ return $this->compare($date)>0; }

	/**
	 * Checks if the objects date is the same as or newer than another date.
	 *
	 * @param Date $date
	 * @return bool
	 */
	function isNewerOrSameLike($date){ return $this->isNewerThan($date) || $this->isTheSame($date); }

	/**
	 * Gets numeric representation of the day of the week.
	 *
	 * First day is Sunday and has number 0.
	 */
	function getWeekDay(){
		return (int)date("w",$this->getUnixTimestamp());
	}
	/**
	 * ISO-8601 numeric representation of the day of the week
	 *
	 * 1 .. Monday
	 * 7 .. Sunday
	 *
	 * @return integer
	 */
	function getIsoWeekDay(){
		$out = $this->getWeekDay();
		if($out==0){ $out = 7; }
		return $out;
	}

	/**
	 * Gets date on next monday.
	 *
	 * For example on Monday 2 this call returns Friday 9
	 * ```
	 * $today = Date::Today();
	 * echo $today->getNextMonday()->toString();
	 * ```
	 *
	 * @return Date
	 */
	function getNextMonday(){ 		return $this->_getNextDay(1);	}

	/**
	 * Gets date on next tuesday closest to this date.
	 *
	 * @return Date
	 */
	function getNextTuesday(){ 		return $this->_getNextDay(2);	}

	/**
	 * Gets date on next wednesday closest to this date.
	 *
	 * @return Date
	 */
	function getNextWednesday(){ 	return $this->_getNextDay(3);	}

	/**
	 * Gets date on next thursday closest to this date.
	 *
	 * @return Date
	 */
	function getNextThursday(){ 	return $this->_getNextDay(4);	}

	/**
	 * Gets date on next friday closest to this date.
	 *
	 * For example on Friday 6 this call returns Friday 13
	 * ```
	 * $today = Date::Today();
	 * echo $today->getNextFriday()->toString();
	 * ```
	 *
	 * @return Date
	 */
	function getNextFriday(){ 		return $this->_getNextDay(5);	}

	/**
	 * Gets date on next saturday closest to this date.
	 *
	 * @return Date
	 */
	function getNextSaturday(){ 	return $this->_getNextDay(6); }

	/**
	 * Gets date on next sunday closest to this date.
	 *
	 * @return Date
	 */
	function getNextSunday(){ 		return $this->_getNextDay(7);	}

	/**
	 * Gets date on monday of the same week as objects date.
	 *
	 * @return Date
	 */
	function getCurrentWeekMonday(){ 		return $this->_getCurrentWeekDay(1);	}

	/**
	 * Gets date on tuesday of the same week as objects date.
	 *
	 * @return Date
	 */
	function getCurrentWeekTuesday(){ 	return $this->_getCurrentWeekDay(2);	}

	/**
	 * Gets date on wednesday of the same week as objects date.
	 *
	 * @return Date
	 */
	function getCurrentWeekWednesday(){ return $this->_getCurrentWeekDay(3);	}

	/**
	 * Gets date on thursday of the same week as objects date.
	 *
	 * @return Date
	 */
	function getCurrentWeekThursday(){ 	return $this->_getCurrentWeekDay(4);	}

	/**
	 * Gets date on friday of the same week as objects date.
	 *
	 * @return Date
	 */
	function getCurrentWeekFriday(){ 		return $this->_getCurrentWeekDay(5);	}

	/**
	 * Gets date on saturday of the same week as objects date.
	 *
	 * @return Date
	 */
	function getCurrentWeekSaturday(){ 	return $this->_getCurrentWeekDay(6); }

	/**
	 * Gets date on sunday of the same week as objects date.
	 *
	 * @return Date
	 */
	function getCurrentWeekSunday(){ 		return $this->_getCurrentWeekDay(7);	}

	/**
	 * Gets date of monday next week.
	 *
	 * @return Date
	 */
	function getNextWeekMonday(){ 		return $this->_getNextWeekDay(1);	}

	/**
	 * Gets date of tuesday next week.
	 *
	 * @return Date
	 */
	function getNextWeekTuesday(){ 	return $this->_getNextWeekDay(2);	}

	/**
	 * Gets date of wednesday next week.
	 *
	 * @return Date
	 */
	function getNextWeekWednesday(){ return $this->_getNextWeekDay(3);	}

	/**
	 * Gets date of thursday next week.
	 *
	 * @return Date
	 */
	function getNextWeekThursday(){ 	return $this->_getNextWeekDay(4);	}

	/**
	 * Gets date of friday next week.
	 *
	 * On Thursday 5 this returns Friday 13.
	 * ```
	 * $today = Date::Today();
	 * echo $today->getNextWeekFriday()->toString();
	 * ```
	 *
	 * @return Date
	 */
	function getNextWeekFriday(){ 		return $this->_getNextWeekDay(5);	}

	/**
	 * Gets date of saturday next week.
	 *
	 * @return Date
	 */
	function getNextWeekSaturday(){ 	return $this->_getNextWeekDay(6); }

	/**
	 * Gets date of sunday next week.
	 *
	 * @return Date
	 */
	function getNextWeekSunday(){ 		return $this->_getNextWeekDay(7);	}

	/**
	 * Gets date for monday previous week.
	 *
	 * @return Date
	 */
	function getPrevWeekMonday(){ 		return $this->_getPrevWeekDay(1);	}

	/**
	 * Gets date for  previous week.
	 *
	 * @return Date
	 */
	function getPrevWeekTuesday(){ 	return $this->_getPrevWeekDay(2);	}

	/**
	 * Gets date for monday previous week.
	 *
	 * @return Date
	 */
	function getPrevWeekWednesday(){ return $this->_getPrevWeekDay(3);	}

	/**
	 * Gets date for monday previous week.
	 *
	 * @return Date
	 */
	function getPrevWeekThursday(){ 	return $this->_getPrevWeekDay(4);	}

	/**
	 * Gets date for monday previous week.
	 *
	 * @return Date
	 */
	function getPrevWeekFriday(){ 		return $this->_getPrevWeekDay(5);	}

	/**
	 * Gets date for monday previous week.
	 *
	 * @return Date
	 */
	function getPrevWeekSaturday(){ 	return $this->_getPrevWeekDay(6); }

	/**
	 * Gets date for monday previous week.
	 *
	 * @return Date
	 */
	function getPrevWeekSunday(){ 		return $this->_getPrevWeekDay(7);	}

	/**
	 * Returns instance of first day of the month of the objects date.
	 *
	 * @return Date
	 */
	function getMonthFirstDay(){ return new Date(Dates::GetFirstDateByDate($this->getDate())); }

	/**
	 * Returns instance of last day of the month of the objects date.
	 *
	 * @return Date
	 */
	function getMonthLastDay(){ return new Date(Dates::GetLastDateByDate($this->getDate())); }

	/**
	 * Returns instance of first day of the month preceding the month in the object.
	 *
	 * @return Date
	 */
	function getPrevMonthFirstDay(){
		$out = $this->getMonthFirstDay();
		$out->addDays(-1);
		return $out->getMonthFirstDay();
	}

	/**
	 * Returns instance of last day of the month preceding the month in the object.
	 *
	 * @return Date
	 */
	function getPrevMonthLastDay(){
		$out = $this->getMonthFirstDay();
		$out->addDays(-1);
		return $out;
	}

	/**
	 * Returns instance of first day of the month following the month in the object.
	 *
	 * @return Date
	 */
	function getNextMonthFirstDay(){
		$out = $this->getMonthLastDay();
		return $out->plusDay();
	}

	/**
	 * @ignore
	 */
	function _getNextDay($iso_week_day){
		$out = $this->plusDay();
		while($out->getIsoWeekDay()!=$iso_week_day){ $out = $out->plusDay(); }
		return $out;
	}

	/**
	 * @ignore
	 */
	function _getCurrentWeekDay($iso_week_day){
		$out = $this->copy();
		if($out->getIsoWeekDay()==$iso_week_day){ return $out; }
		if($iso_week_day<$out->getIsoWeekDay()){
			$out->addDays(-7);
		}
		return $out->_getNextDay($iso_week_day);
	}

	/**
	 * @ignore
	 */
	function _getNextWeekDay($iso_week_day){
		$out = $this->copy();
		$out->addDays(7);
		return $out->_getCurrentWeekDay($iso_week_day);
	}

	/**
	 * @ignore
	 */
	function _getPrevWeekDay($iso_week_day){
		$out = $this->copy();
		$out->addDays(-7);
		return $out->_getCurrentWeekDay($iso_week_day);
	}

	/**
	 * @ignore
	 */
	function _toString($date){
		if(!is_string($date)){ $date = $date->getDate(); }
		return $date;
	}

	/**
	 * Returns the object as string.
	 *
	 * Magic method.
	 */
	function __toString(){ return $this->toString(); }
}

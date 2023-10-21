<?php
class TcDate extends TcBase{
	
	function test_date(){
		$monday = new Date("2008-04-21");
		$tuesday = new Date("2008-04-22");
		$wednesday = new Date("2008-04-23");
		$thursday = new Date("2008-04-24");
		$friday = new Date("2008-04-25");
		$saturday = new Date("2008-04-26");
		$sunday = new Date("2008-04-27");

		$this->assertEquals(1208728800,$monday->getUnixTimestamp());
		$this->assertEquals(1,$monday->getWeekDay());
		$this->assertEquals(1,$monday->getIsoWeekDay());
		$this->assertFalse($monday->isWeekend());

		$this->assertEquals(2,$tuesday->getWeekDay());
		$this->assertEquals(2,$tuesday->getIsoWeekDay());
		$this->assertFalse($tuesday->isWeekend());

		$this->assertEquals(3,$wednesday->getWeekDay());
		$this->assertEquals(3,$wednesday->getIsoWeekDay());
		$this->assertFalse($wednesday->isWeekend());

		$this->assertEquals(4,$thursday->getWeekDay());
		$this->assertEquals(4,$thursday->getIsoWeekDay());
		$this->assertFalse($thursday->isWeekend());

		$this->assertEquals(5,$friday->getWeekDay());
		$this->assertEquals(5,$friday->getIsoWeekDay());
		$this->assertFalse($friday->isWeekend());

		$this->assertEquals(6,$saturday->getWeekDay());
		$this->assertEquals(6,$saturday->getIsoWeekDay());
		$this->assertTrue($saturday->isWeekend());

		$this->assertEquals(0,$sunday->getWeekDay());
		$this->assertEquals(7,$sunday->getIsoWeekDay());
		$this->assertTrue($saturday->isWeekend());


		$next_monday = $monday->getNextMonday();
		$this->assertEquals("2008-04-28",$next_monday->toString());
		$next_tusday = $monday->getNextTuesday();
		$this->assertEquals("2008-04-22",$next_tusday->toString());
		$next_tusday = $tuesday->getNextTuesday();
		$this->assertEquals("2008-04-29",$next_tusday->toString());

		$first_day = $monday->getMonthFirstDay();
		$this->assertEquals("2008-04-01",$first_day->toString());

		$last_day = $monday->getMonthLastDay();
		$this->assertEquals("2008-04-30",$last_day->toString());

		$next = $monday->plusDay();
		$this->assertEquals("2008-04-21",$monday->toString()); // datum v $monday zustava porad stejny!!
		$this->assertEquals("2008-04-22",$next->toString());

		$next = $monday->plusDays(2);
		$this->assertEquals("2008-04-21",$monday->toString());
		$this->assertEquals("2008-04-23",$next->toString());

	}

	function test_get_prev_month_first_and_last_day(){
		$d = new Date("2008-04-23");
		$prev = $d->getPrevMonthFirstDay();
		$prev_last = $d->getPrevMonthLastDay();

		$this->assertEquals("2008-04-23",$d->toString());
		$this->assertEquals("2008-03-01",$prev->toString());
		$this->assertEquals("2008-03-31",$prev_last->toString());

		$d2 = new Date("2008-04-01");
		$prev2 = $d2->getPrevMonthFirstDay();
		$prev2_last = $d2->getPrevMonthLastDay();

		$this->assertEquals("2008-04-01",$d2->toString());
		$this->assertEquals("2008-03-01",$prev2->toString());
		$this->assertEquals("2008-03-31",$prev2_last->toString());
	}

	function test_get_nextv_month_first_day(){
		$d = new Date("2008-04-23");
		$prev = $d->getNextMonthFirstDay();

		$this->assertEquals("2008-04-23",$d->toString());
		$this->assertEquals("2008-05-01",$prev->toString());

		$d2 = new Date("2008-04-30");
		$prev2 = $d2->getNextMonthFirstDay();

		$this->assertEquals("2008-04-30",$d2->toString());
		$this->assertEquals("2008-05-01",$prev2->toString());
	}

	function test_today_etc(){
		$current_date = date("Y-m-d");
		$yesterday_date = date("Y-m-d",strtotime($current_date)-60*60);
		$tomorrow_date = Dates::AddDays($current_date,1);

		$today = Date::Today();
		$yesterday = Date::Yesterday();
		$tomorrow = Date::Tomorrow();

		$this->assertEquals($current_date,$today->toString());
		$this->assertTrue($today->isToday());
		$this->assertFalse($today->isTomorow());
		$this->assertFalse($today->isYesterday());
		$this->assertEquals(0,$today->daysFromToday());
		$this->assertFalse($today->isFuture());
		$this->assertFalse($today->isPast());

		$this->assertEquals($yesterday_date,$yesterday->toString());
		$this->assertFalse($yesterday->isToday());
		$this->assertFalse($yesterday->isTomorow());
		$this->assertTrue($yesterday->isYesterday());
		$this->assertEquals(-1,$yesterday->daysFromToday());
		$this->assertFalse($yesterday->isFuture());
		$this->assertTrue($yesterday->isPast());

		$this->assertEquals($tomorrow_date,$tomorrow->toString());
		$this->assertFalse($tomorrow->isToday());
		$this->assertTrue($tomorrow->isTomorow());
		$this->assertFalse($tomorrow->isYesterday());
		$this->assertEquals(1,$tomorrow->daysFromToday());
		$this->assertTrue($tomorrow->isFuture());
		$this->assertFalse($tomorrow->isPast());

		$this->assertTrue($tomorrow->isNewerThan($today));
		$this->assertFalse($tomorrow->isOlderThan($today));
		$this->assertFalse($today->isNewerThan($tomorrow));
		$this->assertTrue($today->isOlderThan($tomorrow));
		$this->assertFalse($tomorrow->isNewerThan($tomorrow));
		$this->assertFalse($tomorrow->isOlderThan($tomorrow));
	}

	function test_count_days(){
		$d1 = Date::ByDate("2010-05-26");

		$d2 = Date::ByDate("2010-05-26");
		$this->assertEquals(0,$d1->daysFrom($d2));

		$d2 = Date::ByDate("2010-05-01");
		$this->assertEquals(25,$d1->daysFrom($d2));

		$this->assertEquals(24,$d1->daysFrom("2010-05-02"));

		$d1 = Date::ByDate("2010-01-01");
		$d2 = Date::ByDate("2011-01-01");
		$this->assertEquals(365,$d2->daysFrom($d1));

		$d1 = Date::ByDate("2012-01-01");
		$d2 = Date::ByDate("2013-01-01");
		$this->assertEquals(366,$d2->daysFrom($d1)); // prestupny rok
	}

	function test_compare(){
		$d1 = Date::Today();
		$d2 = Date::Today();

		$this->assertEquals(0,$d1->compare($d2));
		$this->assertEquals(0,$d1->compare($d2->toString()));

		$d2 = Date::Tomorrow();
		$this->assertEquals(-1,$d1->compare($d2));
		$this->assertEquals(-1,$d1->compare($d2->toString()));

		$d2 = Date::Yesterday();
		$this->assertEquals(1,$d1->compare($d2));
		$this->assertEquals(1,$d1->compare($d2->toString()));

		$d = Date::ByDate("2008-01-12");

		$this->assertTrue($d->isNewerThan("2008-01-11"));
		$this->assertFalse($d->isNewerThan("2008-01-12"));
		$this->assertTrue($d->isNewerOrSameLike("2008-01-11"));
		$this->assertTrue($d->isNewerOrSameLike("2008-01-12"));
		$this->assertFalse($d->isNewerOrSameLike("2008-01-13"));

		$this->assertTrue($d->isOlderThan("2008-01-14"));
		$this->assertFalse($d->isOlderThan("2008-01-12"));
		$this->assertTrue($d->isOlderOrSameLike("2008-01-14"));
		$this->assertTrue($d->isOlderOrSameLike("2008-01-12"));
		$this->assertFalse($d->isOlderOrSameLike("2008-01-11"));
	}

	function test_by_date(){
		$d1 = Date::ByDate("2001-04-04");
		$d2 = Date::ByDate("2001-02-32");

		$this->assertEquals("2001-04-04",$d1->toString());
		$this->assertNull($d2);

		$d3 = Date::ByDate(array("day" => 1, "month" => 3, "year" => 2005));
		$d4 = Date::ByDate(array("day" => 1, "month" => 13, "year" => 2005));

		$this->assertEquals("2005-03-01",$d3->toString());
		$this->assertNull($d4);

		$d5 = Date::ByDate($d1);
		$this->assertEquals($d1->toString(),$d5->toString());
	}

	function test_by_date_with_time(){
		$d = Date::ByDate("2001-02-03 12:30:00");
		$this->assertEquals("2001-02-03",$d->toString());
	}

	function test_is_monday(){
		$monday = Date::ByDate("2008-05-05");
		$tuesday = Date::ByDate("2008-05-06");
		$wednesday = Date::ByDate("2008-05-07");
		$thursday = Date::ByDate("2008-05-08");
		$friday = Date::ByDate("2008-05-09");
		$saturday = Date::ByDate("2008-05-10");
		$sunday = Date::ByDate("2008-05-11");

		$days = array($monday,$tuesday,$wednesday,$thursday,$friday,$saturday,$sunday);
		$functions = array("isMonday","isTuesday","isWednesday","isThursday","isFriday","isSaturday","isSunday");
		foreach($days as $i => $day){
			foreach($functions as $j => $fnc){
				$_expected = false;
				if($i==$j){ $_expected = true; }
				$this->assertEquals($_expected,$day->$fnc());
			}
		}
	}	

	function test_get_current_week_monday(){
		$monday = Date::ByDate("2008-05-05");
		$friday = Date::ByDate("2008-05-09");

		// -- 

		$d = $monday->getCurrentWeekMonday();
		$this->assertEquals("2008-05-05",$d->toString());

		$d = $monday->getCurrentWeekTuesday();
		$this->assertEquals("2008-05-06",$d->toString());

		$d = $monday->getCurrentWeekSunday();
		$this->assertEquals("2008-05-11",$d->toString());

		// -- 
	
		$d = $friday->getCurrentWeekMonday();
		$this->assertEquals("2008-05-05",$d->toString());

		$d = $friday->getCurrentWeekTuesday();
		$this->assertEquals("2008-05-06",$d->toString());

		$d = $friday->getCurrentWeekFriday();
		$this->assertEquals("2008-05-09",$d->toString());

		$d = $friday->getCurrentWeekSunday();
		$this->assertEquals("2008-05-11",$d->toString());
	}

	function test_to_string(){
		$date = Date::ByDate("2001-01-31");
		$this->assertEquals("2001-01-31",$date->toString());
		$this->assertEquals("31.1.2001",$date->toString("j.n.Y"));
	}

	function test_add_years(){
		$date = Date::ByDate("2008-01-01");
		$date->addYear();
		$this->assertEquals("2009-01-01",$date->toString());

		$date = Date::ByDate("2008-02-29");
		$date->addYear();
		$this->assertEquals("2009-02-28",$date->toString());

		$date = Date::ByDate("2008-02-29");
		$date->addYears(4);
		$this->assertEquals("2012-02-29",$date->toString());

		$date = Date::ByDate("2008-04-21");
		$next = $date->plusYear();
		$this->assertEquals("2008-04-21",$date->toString());
		$this->assertEquals("2009-04-21",$next->toString());

		$next = $date->plusYears(2);
		$this->assertEquals("2008-04-21",$date->toString());
		$this->assertEquals("2010-04-21",$next->toString());
	}

	function test_add_months(){
		$date = Date::ByDate("2008-02-29");
		$date->addMonths(12);
		$this->assertEquals("2009-02-28",$date->toString());

		$date = Date::ByDate("2008-03-31");
		$date->remMonths(13);
		$this->assertEquals("2007-02-28",$date->toString());

		$date = Date::ByDate("2008-02-29");
		$date2 = $date->plusMonth();
		$this->assertEquals("2008-02-29",$date->toString());
		$this->assertEquals("2008-03-29",$date2->toString());

		$date = Date::ByDate("2008-01-31");
		$date2 = $date->minusMonth();
		$this->assertEquals("2008-01-31",$date->toString());
		$this->assertEquals("2007-12-31",$date2->toString());
	}

	function test_to_gmdate(){
		$date = Date::ByDate("2008-02-29");
		$this->assertEquals(gmdate('D, d M Y H:i:s \G\M\T',$date->getUnixTimestamp()),$date->toGmdate());
	}

	function test_getUnixTimestamp(){
		$date = new Date("2023-10-21");
		$this->assertEquals(1697839200,$date->getUnixTimestamp());

		$date = new Date("nonsence");
		$this->assertNull($date->getUnixTimestamp());
	}
}	

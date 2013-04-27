<?php
class TcDates extends TcBase{
	function test_add_months(){
		$this->assertFalse(Dates::AddMonths("2007-02-29",1)); // neplatne datum
		$this->assertEquals("2007-02-28",Dates::AddMonths("2007-02-28",0)); // nic se nemeni

		$this->assertEquals("2008-02-01",Dates::AddMonths("2008-01-01",1));
		$this->assertEquals("2009-01-01",Dates::AddMonths("2008-01-01",12));

		$this->assertEquals("2007-02-28",Dates::AddMonths("2007-01-31",1)); // neprestupny rok
		$this->assertEquals("2008-02-29",Dates::AddMonths("2008-01-31",1)); // prestupny rok

		$this->assertEquals("2008-01-29",Dates::AddMonths("2008-02-29",-1));
		$this->assertEquals("2007-12-29",Dates::AddMonths("2008-02-29",-2));
		$this->assertEquals("2007-01-01",Dates::AddMonths("2008-01-01",-12));

		$this->assertEquals("2007-02-28",Dates::AddMonths("2008-02-29",-12));
		$this->assertEquals("2007-01-29",Dates::AddMonths("2008-02-29",-13));

		$this->assertEquals("2000-01-01",Dates::AddMonths("2008-01-01",-12*8));
		$this->assertEquals("1999-12-01",Dates::AddMonths("2008-01-01",-12*8-1));
		$this->assertEquals("2000-02-01",Dates::AddMonths("2008-01-01",-12*8+1));
	}
}

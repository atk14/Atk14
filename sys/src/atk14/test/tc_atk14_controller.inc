<?php
class tc_atk14_controller extends tc_base{
	function test_multiple_before_filters(){
		$c = new MultipleBeforeFiltersController();
		$c->atk14__initialize(array());

		$c->user = 1; // simulace prihlaseneho uzivatele
		$this->assertEquals(array(),$c->before_filters);
		$c->atk14__runBeforeFilters();
		$this->assertEquals(array("filter1","filter2","check_user_is_logged","before_filter","filter3","filter4"),$c->before_filters);

		$c->user = null; // simulace odhlaseneho uzivatele
		$c->before_filters = array();
		$this->assertEquals(array(),$c->before_filters);
		$c->atk14__runBeforeFilters();
		$this->assertEquals(array("filter1","filter2","check_user_is_logged"),$c->before_filters);
	}

	function test_multiple_after_filters(){
		$c = new MultipleAfterFiltersController();
		$c->atk14__initialize();

		$this->assertEquals(array(),$c->after_filters);
		$c->atk14__runAfterFilters();
		$this->assertEquals(array("afilter1","afilter2","after_filter","afilter3","afilter4"),$c->after_filters);
	}
}
?>

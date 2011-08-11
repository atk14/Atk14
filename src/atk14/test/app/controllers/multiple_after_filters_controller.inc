<?php
class MultipleAfterFiltersController extends ApplicationController{

	function _initialize(){
		$this->after_filters = array();

		$this->_prepend_after_filter("afilter2");
		$this->_prepend_after_filter("afilter1");
		$this->_append_after_filter("afilter3");
		$this->_append_after_filter("afilter4");
	}

	function _afilter1(){
		$this->after_filters[] = "afilter1";
	}

	function _afilter2(){
		$this->after_filters[] = "afilter2";
	}

	function _after_filter(){
		$this->after_filters[] = "after_filter";
	}

	function _afilter3(){
		$this->after_filters[] = "afilter3";
	}

	function _afilter4(){
		$this->after_filters[] = "afilter4";
	}
}

?>

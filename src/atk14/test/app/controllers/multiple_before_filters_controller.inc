<?php
class MultipleBeforeFiltersController extends ApplicationController{

	function _initialize(){
		$this->before_filters = array();

		$this->_prepend_before_filter("check_user_is_logged");
		$this->_prepend_before_filter("filter2");
		$this->_prepend_before_filter("filter1");
		$this->_append_before_filter("filter3");
		$this->_append_before_filter("filter4");
	}

	function _filter1(){
		$this->before_filters[] = "filter1";
	}

	function _filter2(){
		$this->before_filters[] = "filter2";
	}

	function _check_user_is_logged(){
		$this->before_filters[] = "check_user_is_logged";

		if(!$this->user){
			$this->response->forbidden();
		}
	}

	function _before_filter(){
		$this->before_filters[] = "before_filter";
	}

	function _filter3(){
		$this->before_filters[] = "filter3";
	}

	function _filter4(){
		$this->before_filters[] = "filter4";
	}
}

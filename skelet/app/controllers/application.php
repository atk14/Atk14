<?php
class ApplicationController extends Atk14Controller{
	function index(){
		// acts like there's no index action by default
		$this->_execute_action("error404");
	}

	function error404(){
		$this->page_title = "Page not found";
		$this->response->setStatusCode(404);
		$this->template_name = "application/error404";
		if($this->request->xhr()){
			// there's no need to render anything for XHR requests
			$this->render_template = false;
		}
	}

	function _initialize(){
		$this->_prepend_before_filter("application_before_filter");

		if(!$this->rendering_component){
			// keep these lines at the end of the _initialize() method
			$this->_prepend_before_filter("begin_database_transaction"); // _begin_database_transaction() is the very first before filter
			$this->_append_after_filter("end_database_transaction"); // _end_database_transaction() is the very last after filter
		}
	}

	function _before_filter(){
		// therer's nothing here...
		// you can safely cover this method in a descendand without calling the parent
	}

	function _application_before_filter(){
		$this->response->setContentType("text/html");
		$this->response->setContentCharset(DEFAULT_CHARSET);

		// following header helps to avoid clickjacking attacks
		$this->response->setHeader("X-Frame-Options","SAMEORIGIN"); // SAMEORIGIN, DENY
	}

	function _begin_database_transaction(){
		$this->dbmole->begin();
	}

	function _end_database_transaction(){
		if(TEST){ return; } // perhaps you don't want to commit a transaction when you are testing
		$this->dbmole->commit();
	}
}

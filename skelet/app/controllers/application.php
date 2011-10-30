<?
class ApplicationController extends Atk14Controller{
	function index(){
		$this->_execute_action("error404");
	}

	function error404(){
		$this->page_title = "Page not found";
		$this->response->setStatusCode(404);
		$this->template_name = "application/error404";
	}

	function _initialize(){
		$this->_prepend_before_filter("application_before_filter");

		if(!$this->rendering_component){
			// Definujme toto jako posledni krok v _initialize()!
			// Docilime toho, ze filtr _begin_database_transaction() bude zavolan uplne jako prvni before filter
			// a _end_database_transaction() zase jako posledni after filtr.
			$this->_prepend_before_filter("begin_database_transaction");
			$this->_append_after_filter("end_database_transaction");
		}
	}

	function _application_before_filter(){
		$this->response->setContentType("text/html");
		$this->response->setContentCharset("UTF-8");
	}

	function _begin_database_transaction(){
		$this->dbmole->begin();
	}

	function _end_database_transaction(){
		$this->dbmole->commit();
	}
}

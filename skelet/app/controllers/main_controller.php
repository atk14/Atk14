<?php
class MainController extends ApplicationController{

	/**
	 * The front page
	 * 
	 * See corresponding template: app/views/main/index.tpl
	 * See default layout: app/layouts/_default.tpl
	 */
	function index(){
		$this->page_title = _("Welcome!");
	}
}

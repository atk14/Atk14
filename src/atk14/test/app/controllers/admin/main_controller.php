<?php
class MainController extends ApplicationController{
	function index(){
	}

	function _before_filter(){
		assert(class_exists("AdminForm"));
	}
}

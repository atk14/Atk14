<?php
class MainController extends AdminController{
	function index(){
	}

	function _before_filter(){
		assert(class_exists("AdminForm"));
	}
}

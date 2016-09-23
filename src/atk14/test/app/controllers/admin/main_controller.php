<?php
class MainController extends AdminController{
	function index(){
	}

	function _before_filter(){
		if(!class_exists("AdminForm")){
			throw new Exception("MainController: class AdminForm doesn't exist");
		}
	}
}

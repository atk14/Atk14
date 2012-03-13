<?php
class TestingController extends ApplicationController{
	function default_layout(){

	}

	function no_layout(){
		$this->render_layout = false;
	}

	function custom_layout(){
		$this->layout_name = "custom";
	}

	function _before_filter(){
		$this->template_name = "template";
	}

}

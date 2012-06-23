<?php
class TestingController extends ApplicationController{
	function test(){
		$this->template_name = "test"; // see _before_filter();
	}


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

	function _before_render(){
		$this->smarty->assign("value_assigned_directly_from_before_render","there_is_a_value_assigned_directly_from_before_render");
		$this->tpl_data["value_assigned_usually_from_before_render"] = "there_is_a_value_assigned_usually_from_before_render";
	}
}

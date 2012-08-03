<?php
class TestingController extends ApplicationController{
	function test(){
		$this->tpl_data["value_assigned_from_action_method"] = "there_is_a_value_assigned_from_action_method";
	}

	function test_render(){
		$this->tpl_data["firstname"] = "John";
		$this->snippet = $this->tpl_data["snippet"] = $this->_render("shared/user_detail",array(
			"lastname" => "Doe"
		));
	}

	function default_layout(){
		$this->template_name = "template";
	}

	function no_layout(){
		$this->template_name = "template";
		$this->render_layout = false;
	}

	function custom_layout(){
		$this->template_name = "template";
		$this->layout_name = "custom";
	}

	function _before_filter(){
	}

	function _before_render(){
		$this->smarty->assign("value_assigned_directly_from_before_render","there_is_a_value_assigned_directly_from_before_render");
		$this->tpl_data["value_assigned_usually_from_before_render"] = "there_is_a_value_assigned_usually_from_before_render";
	}
}
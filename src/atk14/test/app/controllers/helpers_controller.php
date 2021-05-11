<?php
class HelpersController extends ApplicationController{
	function javascript_script_tag(){ }
	function sortable(){
		$this->sorting->add("date");
		$this->sorting->add("name");
	}

	function h(){
		$this->tpl_data["title"] = "The book <strong>is mine!</strong>";
	}

	function render(){
		$this->tpl_data["books"] = array(
			array("title" => "The Adventures of Tom Sawyer", "author" => "Mark Twain"),
			array("title" => "Swallows and Amazons", "author" => "Arthur Ransome"),
		);
	}

	function render_with_forms(){
		$this->form = $this->_get_form("FirstForm");
		$this->tpl_data["second_form"] = $this->_get_form("SecondForm");
	}

	function render_component(){
		
	}

	function render_component_with_redirection(){
		
	}

	function a(){
		$this->lang = "en";
	}

	function link_to(){
	}

	function content(){
		$this->layout_name = "testing_content_helper";
		$this->render_layout = true;
	}

	function cache(){
		$this->tpl_data["uniqid"] = uniqid();
	}

	function _before_filter(){
		$this->render_layout = false;
	}
}

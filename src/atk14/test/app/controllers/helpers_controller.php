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

	function render_component(){
		
	}

	function a(){
		$this->lang = "en";
	}

	function link_to(){
	}

	function content(){
		//require_once(dirname(__FILE__)."/../../../helpers/block.placeholder.php");
		$this->layout_name = "testing_content_helper";
		$this->render_layout = true;
	}

	function _before_filter(){
		$this->render_layout = false;
	}
}

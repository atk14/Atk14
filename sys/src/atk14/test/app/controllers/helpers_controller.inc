<?php
class HelpersController extends ApplicationController{
	function javascript_script_tag(){ }
	function sortable(){
		$this->sorting->add("date");
		$this->sorting->add("name");
	}

	function _before_filter(){
		$this->render_layout = false;
	}
}
?>

<?php
class MainController extends ApplicationController{
	function index(){
		$this->render_template = false;
	}

	function hello_world(){
		
	}

	function writing_to_session(){
		$this->render_template = false;
		$this->session->s("fruit","pineapple");
	}
}

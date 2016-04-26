<?php
class MainController extends ApplicationController{

	function index(){
		$this->render_template = false;
	}

	function hello_world(){
		
	}

	function hello_from_earth(){
		$this->render_template = false;
		$this->response->write("Hello from Earth!");
	}

	function hello_from_mars(){
		$this->response->write("Hello from Mars!");
	}

	function hello_from_venus(){
		$this->response->write("Hello from Venus!");
	}

	function writing_to_session(){
		$this->render_template = false;
		$this->session->s("fruit","pineapple");
	}

	function _before_filter(){
		if($this->action=="hello_from_venus"){
			$this->render_template = false;
		}
	}

	function _before_render(){
		if($this->action=="hello_from_mars"){
			$this->render_template = false;
		}
	}
}

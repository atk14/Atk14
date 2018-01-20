<?php
class ExecuteActionsController extends ApplicationController {

	function action_one(){

		if($this->params->defined("execute_action_two")){
			return $this->_execute_action("action_two");
		}

		if($this->params->defined("execute_action_three")){
			return $this->_execute_action("action_three");
		}

		$this->render_template = false;
		$this->response->write("action_one executed");
	}

	function action_two(){

		$this->render_template = false;
		$this->response->write("action_two executed");
	}

	function action_three(){

		$this->render_template = false;
		$this->response->write("action_three executed");
	}
}

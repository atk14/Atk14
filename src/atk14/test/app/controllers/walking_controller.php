<?php
class WalkingController extends ApplicationController {

	function walk(){
		$this->_walk(array(
			"step1",
			"step2",
			"step3",
		));
	}

	function walk__step1(){
		$this->page_title = "Step #1";

		if($this->request->post()){
			return "step1 finished successfully";
		}
	}

	function walk__step2(){
		$this->page_title = "Step #2";

		if($this->params->getString("straight_to_step3")){
			$this->_next_step("step2 finished gracefully");
		}
	}

	function walk__step3(){
		$this->page_title = "Step #3";
	}

	function _before_walking(){
		$this->tpl_data["returned_by"] = $this->returned_by;
	}
}

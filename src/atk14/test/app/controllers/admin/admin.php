<?php
class AdminController extends Atk14Controller{
	function error404(){
		$this->response->setStatusCode(404);
		$this->render_layout = false;
	}
}

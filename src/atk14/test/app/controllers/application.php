<?php
#[\AllowDynamicProperties]
class ApplicationController extends Atk14Controller{

	function error404(){
		$this->response->setStatusCode(404);
		$this->render_layout = false;
		$this->template_name = "application/error404"; // this has to be defined with the directory name because of other namespaces
	}
}

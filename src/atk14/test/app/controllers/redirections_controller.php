<?php
class RedirectionsController extends ApplicationController{
	function index(){
		$location = $this->params->defined("location") ? $this->params->getString("location") : "http://www.atk14.net/";
		$this->_redirect_to($location);
	}
}

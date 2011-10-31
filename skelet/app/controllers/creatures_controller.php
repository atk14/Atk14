<?php
/**
* Sample controller.
* You can find there all the typical actions which a typical controller has.
*/
class CreaturesController extends ApplicationController{
	
	/**
	* Lists creatures.
	* Has a "fulltext" capability :)
	*/
	function index(){
		($d = $this->form->validate($this->params)) || ($d = $this->form->get_initial());

		$conditions = array();
		$bind_ar = array();
		if($d["q"]){
			$conditions[] = "UPPER(name) LIKE UPPER(:q)";
			$bind_ar[":q"] = "%$d[q]%";
		}

		$this->tpl_data["finder"] = Creature::Finder(array(
			"conditions" => $conditions,
			"bind_ar" => $bind_ar
		));
	}

	/**
	* Creates a new creature.
	*/
	function create_new(){
		if($this->request->post() && ($d = $this->form->validate($this->params))){
			Creature::CreateNewRecord($d);
			$this->flash->success(_("A new creature has been successfuly created"));
			$this->_redirect_to_action("index");
		}
	}

	/**
	* Displays the given creature.
	*/
	function detail(){
		$this->page_title = sprintf(_("Detail of the creature #%s"),$this->creature->getId());

		if($this->params->getString("format")=="json"){
			$this->render_template = false;
			$this->response->setContentType("text/plain");
			$this->response->write($this->creature->toJson());
		}
		if($this->params->getString("format")=="xml"){
			$this->render_template = false;
			$this->response->setContentType("text/xml");
			$this->response->writeln('<'.'?xml version="1.0" encoding="UTF-8"?'.'>');
			$this->response->write($this->creature->toXml());
		}
	}

	/**
	* Edits the given creature.
	*/
	function edit(){
		$this->page_title = sprintf(_("Editing the creature #%s"),$this->creature->getId());

		$this->form = Atk14Form::GetForm("CreateNewForm");
		$this->form->set_initial($this->creature);

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			$this->creature->s($d);
			$this->flash->success(_("The creature has been changed successfuly."));
			$this->_redirect_to_action("index");
		}
	}

	/**
	* Deletes the given creature.
	* This action will do it`s job only when the request method is POST.
	*/
	function destroy(){
		if(!$this->request->post()){ return $this->_execute_action("error404"); }
		$this->creature->destroy();
	}

	/**
	* Sets the controller`s state.
	* Will be executed before the action method.
	*/
	function _before_filter(){
		if(in_array($this->action,array("detail","edit","destroy")) && !$this->_find_record()){
			$this->_execute_action("error404");
		}
	}

	/**
	* Finds a creature according the "id" parameter (in URI or in POSTed params).
	*/
	function _find_record(){
		return $this->creature = $this->tpl_data["creature"] = Creature::GetInstanceById($this->params->getInt("id"));
	}
}

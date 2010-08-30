<?
class CreaturesController extends ApplicationController{
	function index(){
		if($d = $this->form->validate($this->params)){
			$conditions = array();
			$bind_ar = array();
			if($d["q"]){
				$conditions[] = "UPPER(name) LIKE UPPER(:q)";
				$bind_ar[":q"] = "%$d[q]%";
			}
			$this->tpl_data["finder"] = Model::Finder(array(
				"class_name" => "Creature",
				"conditions" => $conditions,
				"bind_ar" => $bind_ar
			));
		}
	}

	function create_new(){
		if($this->request->post() && ($d = $this->form->validate($this->params))){
			Creature::CreateNewRecord($d);
			$this->flash->success(_("A new creature has been successfuly created"));
			$this->_redirect_to_action("index");
		}
	}

	function detail(){
		$this->page_title = sprintf(_("Detail of the creature %s"),"#".$this->creature->getId());
	}

	function edit(){
		$this->page_title = sprintf(_("Editing the creature %s"),"#".$this->creature->getId());

		$this->form = Atk14Form::GetForm("CreateNewForm");
		$this->form->set_initial($this->creature);

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			$this->creature->s($d);
			$this->flash->success(_("The creature has been changed successfuly."));
			$this->_redirect_to_action("index");
		}
	}

	function destroy(){
		if(!$this->request->post()){ return $this->_execute_action("error404"); }
	}

	function _before_filter(){
		if(in_array($this->action,array("detail","edit","destroy")) && !$this->_find_record()){
			$this->_execute_action("error404");
		}
	}

	function _find_record(){
		if($this->creature = Creature::GetInstanceById($this->params->getInt("id"))){
			$this->tpl_data["creature"] = $this->creature;
		}
		return $this->creature;
	}
}

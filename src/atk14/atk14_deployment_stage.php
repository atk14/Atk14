<?php
class Atk14DeploymentStage{
	protected $data;

	protected function __construct($name,$params){
		// converting strings into arrays
		foreach(array("before_deploy", "after_deploy", "rsync") as $k){
			if(!is_array($params[$k])){
				if(!$params[$k]){ $params[$k] = array(); continue; } // empty string
				$params[$k] = array($params[$k]);
			}
		}

		// converting string into boolean
		foreach(array("create_maintenance_file") as $k){
			$params[$k] = String::ToObject($params[$k])->toBoolean();
		}

		!preg_match('/\/$/',$params["directory"]) && ($params["directory"] .= "/"); // "projects/myapp" -> "projects/myapp/"
		
		$params["name"] = $name;

		$this->data = new Dictionary($params);
	}

	/**
	 * $stages = Atk14DeploymentStage::GetStages();
	 */
	static function GetStages(){
		global $ATK14_GLOBAL;

		$out = array();
	
		$very_very_defauls = array(
			"url" => "http://www.example.com/", // just for information
			"user" => null,
			"server" => null,
			"directory" => null,
			"create_maintenance_file" => "true",
			"deploy_via" => "git_push", // there is only one way
			"deploy_repository" => null,
			"deploy_branch" => "master",
			"before_deploy" => array(),
			"rsync" => array(), // array of directories which have to be synchronized to the server
			"after_deploy" => array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),
		);

		$defaults = null;
		foreach($ATK14_GLOBAL->getConfig("deploy") as $name => $ar){
			if(!isset($defaults)){
				$defaults = $ar + $very_very_defauls;
			}

			$ar += $defaults;
			$out[$name] = new Atk14DeploymentStage($name,$ar);
		}

		return $out;
	}

	/**
	 * $preview = Atk14DeploymentStage::GetStage("preview"):
	 */
	static function GetStage($name){
		foreach(Atk14DeploymentStage::GetStages() as $s){
			if($s->name==$name){ return $s; }
		}
	}

	function toArray(){
		return $this->data->toArray();
	}

	function __toString(){
		return "deployment_stage_$this->name";
	}

	function __get($name){
		if($this->data->defined($name)){
			return $this->data[$name];
		}
		throw new Exception("Accessed to unknown value $name");
	}

	function __set($name,$value){
		throw new Exception("Sorry, $this is read only");
	}

}

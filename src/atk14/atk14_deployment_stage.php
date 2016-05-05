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

		// converting strings into booleans
		foreach(array("create_maintenance_file") as $k){
			$params[$k] = String4::ToObject($params[$k])->toBoolean();
		}

		!preg_match('/\/$/',$params["directory"]) && ($params["directory"] .= "/"); // "projects/myapp" -> "projects/myapp/"
		
		$params["name"] = $name;

		$this->data = new Dictionary($params);
	}

	/**
	 * $stages = Atk14DeploymentStage::GetStages();
	 *
	 * foreach($stages as $name => $stage){
	 *
	 * }
	 */
	static function GetStages(){
		global $ATK14_GLOBAL;

		$out = array();
	
		$very_very_defauls = array(
			"url" => "", // just for information; e.g. "http://www.example.com/"
			"user" => null,
			"server" => null,
			"port" => null, // ssh port, e.g. "2222"
			"directory" => null,
			"create_maintenance_file" => "false",
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

			/*
			// TODO: add some checks
			$raw_def_keys = array_keys($raw_def);
			foreach($config as $k => $v){
				if(!in_array($k,$raw_def_keys)){
					echo "in section $stage there is an unknown key \"$k\" (in config/deploy.yml)\n";
					_exit_with_errors();
				}
				// TODO: check mandatory values
				// TODO: check formats of values
			} */

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

	static function GetFirstStage(){
		foreach(Atk14DeploymentStage::GetStages() as $s){ return $s; }
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

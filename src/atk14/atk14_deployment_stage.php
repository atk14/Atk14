<?php
/**
 * Class for working with deployment stages.
 *
 * @filesource
 */

/**
 * Class for working with deployment stages.
 *
 * @package Atk14\Core
 * @todo Some description explanation
 */
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
	 * Returns all deployment stages
	 *
	 * ```
	 * $stages = Atk14DeploymentStage::GetStages();
	 *
	 * foreach($stages as $name => $stage){
	 *
	 * }
	 * ```
	 * @return Atk14DeploymentStage[]
	 */
	static function GetStages(){
		global $ATK14_GLOBAL;

		$out = array();
		$defaults =
	
		$very_very_defauls = array(
			"extends" => null, // e.g. "production"
			"url" => "", // e.g. http://www.example.com; just for information
			"user" => null,
			"server" => null,
			"port" => null, // ssh port, e.g. "2222"
			"env" => "", // e.g. "PATH=/home/john/bin/:$PATH EDITOR=vim"; environment variable ATK14_ENV=production is set automatically
			"directory" => null,
			"create_maintenance_file" => "false",
			"deploy_via" => "git_push", // there is only one way
			"deploy_repository" => null,
			"deploy_branch" => "master",
			"before_deploy" => array(),
			"rsync" => array(), // array of directories which have to be synchronized to the server
			"after_deploy" => array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),
		);
		$required_key_order = array_keys($very_very_defauls);

		$defaults = null;
		$all_defaults = array();
		foreach($ATK14_GLOBAL->getConfig("deploy") as $name => $ar){
			if(!isset($defaults)){
				// the default recipe is the one for the first stage
				$defaults = $ar + $very_very_defauls;
			}

			$_defaults = $defaults;
			if(isset($ar["extends"]) && strlen($ar["extends"])>0){
				if(!isset($all_defaults[$ar["extends"]])){
					throw new Exception("Deployment stage $name extends from unknown ($ar[extends])");
				}
				$_defaults = $all_defaults[$ar["extends"]];
			}

			$ar += $_defaults;

			$all_defaults[$name] = $ar;

			$ar = self::_ReplaceVariables($ar,$name);

			if(strlen($ar["deploy_repository"]) && strpos($ar["deploy_repository"],":")===false && strlen($ar["server"])){
				$ar["deploy_repository"] = (strlen($ar["user"]) ? "$ar[user]@" : "")."$ar[server]:$ar[deploy_repository]"; // "/home/user/repos/myapp.git" -> "user@server:/home/user/repos/myapp.git"
			}

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

			// gaining the requested key order in $ar
			$_ar = array();
			foreach($required_key_order as $k){
				$_ar[$k] = $ar[$k];
				unset($ar[$k]);
			}
			$_ar += $ar; // addition of eventual custom variables
			$ar = $_ar;

			$out[$name] = new Atk14DeploymentStage($name,$ar);
		}

		return $out;
	}

	function getName(){
		return $this->name;
	}

	function getDirectory(){
		return $this->directory;
	}

	/**
	 * Returns URL of the deploy repository from the outside view
	 *
	 */
	function getDeployRepository(){
		$ar = $this->toArray();
		if(strlen($ar["deploy_repository"]) && strpos($ar["deploy_repository"],":")===false && strlen($ar["server"])){
			$ar["deploy_repository"] = (strlen($ar["user"]) ? "$ar[user]@" : "")."$ar[server]:$ar[deploy_repository]"; // "/home/user/repos/myapp.git" -> "user@server:/home/user/repos/myapp.git"
		}
		return $ar["deploy_repository"];
	}

	/**
	 * Returns URL of the deploy repository from the views of the deployment server
	 *
	 */
	function getDeployRepositoryRemoteDir(){
		$ar = $this->toArray();
		$deploy_repository_remote = $ar["deploy_repository"];
		$deploy_repository_remote = preg_replace('/^.*?:/','',$deploy_repository_remote);
		if(!preg_match('/^\//',$deploy_repository_remote)){ $deploy_repository_remote = "/home/$ar[user]/$deploy_repository_remote"; }
		return $deploy_repository_remote;
	}

	/**
	 *
	 * @return string[]
	 */
	function getRsync(){
		$out = $this->rsync;
		if(!$out){ return array(); }
		if(!is_array($out)){
			$out = array($out);
		}
		return $out;
	}

	/**
	 *
	 * 	$cmd = $stage->compileRemoteShellCommand("./scripts/migrate"); // e.g. 'ssh deploy@devel.mushoomradar.net "cd /home/deploy/webapps/mushoomradar_devel/ && export ATK14_ENV=production && (./scripts/migrate)"'
	 */
	function compileRemoteShellCommand($cmd,$cd_to_project_directory = true){
		$config = $this->toArray();

		$cd_cmd = $cd_to_project_directory ? "cd $config[directory] && " : ""; // e.g. "cd /home/deploy/webapps/myapp/ && "

		$port_spec = $config["port"] ? " -p $config[port]" : "";
		$user = $config["user"] ? "$config[user]@" : "";
		$env = "ATK14_ENV=production";
		$env .= $config["env"] ? " $config[env]" : "";
		$cmd = "ssh $user$config[server]$port_spec \"${cd_cmd}export $env && (".strtr($cmd,array('"' => '\"', "\\" => "\\\\")).")\"";
		return $cmd;
	}

	/**
	 *
	 *	$cmd = $stage->compileRsyncCommand("public/dist/");
	 */
	function compileRsyncCommand($file){
		$config = $this->toArray();
		if(is_dir(ATK14_DOCUMENT_ROOT."/".$file)){
			!preg_match('/\/$/',$file) && ($file .= "/"); // "public/dist" -> "public/dist/"
		}
		$port_spec = $config["port"] ? " -e 'ssh -p $config[port]'" : "";
		$user = $config["user"] ? "$config[user]@" : "";
		$dest = "$config[directory]/$file";
		$dest = preg_replace('/\/{2,}/','/',$dest);
		return "rsync -av --checksum --no-times --delete$port_spec $file $user$config[server]:$dest";
	}

	function toArray(){
		// it's fine to have the name on the first position :)
		$data = $this->data->toArray();
		$out = array("name" => $data["name"]);
		unset($data["name"]);
		return $out + $data;
	}

	/**
	 * Get instance of Atk14DeploymentStage by name
	 *
	 * ```
	 * $preview = Atk14DeploymentStage::GetStage("preview"):
	 * ```
	 *
	 * @param $name string
	 * @return Atk14DeploymentStage
	 * @todo maybe should return null when no stage is found
	 */
	static function GetStage($name){
		foreach(Atk14DeploymentStage::GetStages() as $s){
			if($s->name==$name){ return $s; }
		}
	}

	static function GetFirstStage(){
		foreach(Atk14DeploymentStage::GetStages() as $s){ return $s; }
	}

	protected static function _ReplaceVariables($ar,$name){
		$replaces = array();
		foreach($ar as $key => $value){
			if(is_array($value)){ continue; }
			$replaces['{{'.$key.'}}'] = $value;
		}

		$replaces['{{name}}'] = $name;

		$cnt = 0;
		while(1){
			$something_replaced = false;
			foreach($ar as $key => $value){
				$ar[$key] = self::_ReplaceVariablesInItem($value,$replaces,$_something_replaced);
				$something_replaced = $_something_replaced ? $_something_replaced : $something_replaced;
			}
			if(!$something_replaced){ break; }
			//
			$cnt++;
			if($cnt>10){
				throw new Exception("A circular reference found in deploy.yml in stage $name");
			}
		}

		return $ar;
	}

	protected static function _ReplaceVariablesInItem($item,$replaces,&$something_replaced = false){
		$something_replaced = false;
	
		if(is_null($item)){ return $item; }
		if(is_array($item)){
			foreach($item as $k => $v){
				$_something_replaced = false;
				$item[$k] = self::_ReplaceVariablesInItem($v,$replaces,$_something_replaced);
				$something_replaced = $_something_replaced ? $_something_replaced : $something_replaced;
			}
			return $item;
		}

		// normalizing variable names
		$item = preg_replace('/\{\{ ?([a-z_]+) ?\}\}/','{{\1}}',$item); // {{ user }} -> {{user}}

		$orig = "$item";
		$item = strtr($item,$replaces);
		$something_replaced = $item!==$orig;
		return $item;
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

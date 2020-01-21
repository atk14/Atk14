<?php
class TcDeploymentStage extends TcBase{
	function test(){
		$stages = Atk14DeploymentStage::GetStages();

		$this->assertEquals(4,sizeof($stages));
		$this->assertEquals(array("devel","acceptation","acceptation2","production"),array_keys($stages));

		$first_stage = Atk14DeploymentStage::GetFirstStage();
		$this->assertEquals("devel",$first_stage->name);

		// non existing stage
		$this->assertEquals(null,Atk14DeploymentStage::GetStage("preview"));

		$devel = Atk14DeploymentStage::GetStage("devel");
		$this->assertEquals("deployment_stage_devel","$devel");
		$this->assertEquals("devel",$devel->name);
		$this->assertEquals("devel.mushoomradar.net",$devel->server);
		// etc
		// key order
		$this->assertEquals(array(
			"name",
			"extends",
			"url",
			"user",
			"server",
			"port",
			"env",
			"directory",
			"create_maintenance_file",
			"deploy_via",
			"deploy_repository",
			"deploy_branch",
			"before_deploy",
			"rsync",
			"after_deploy",
		),array_keys($devel->toArray()));
		$this->_compareArrays(array(
			"name" => "devel",
			"user" => "deploy",
			"server" => "devel.mushoomradar.net",
			"port" => null,
			"directory" => "/home/deploy/apps/mushoomradar_devel/",
			"deploy_via" => "git_push",
			"deploy_repository" => "deploy@devel.mushoomradar.net:repos/mushoomradar.git",
			"deploy_branch" => "master",
			"create_maintenance_file" => false,
			"before_deploy" => array("@local composer update", "@local grunt dist"),
			"rsync" => array("public/dist/","vendor/"),
			"after_deploy" => array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),
		),$devel->toArray());

		$production = Atk14DeploymentStage::GetStage("production");
		$this->assertEquals("production",$production->name);
		$this->assertEquals("deployment_stage_production","$production");
		$this->_compareArrays(array(
			"name" => "production",
			"user" => "deploy",
			"server" => "zeus.mushoomradar.net",
			"directory" => "/home/deploy/apps/mushoomradar_production/",
			"deploy_via" => "git_push",
			"deploy_repository" => "deploy@zeus.mushoomradar.net:repos/mushoomradar.git",
			"deploy_branch" => "master",
			"create_maintenance_file" => false,
			"before_deploy" => array("@local composer update", "@local grunt dist"),
			"rsync" => array("public/dist/","vendor/"),
			"after_deploy" => array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),
		),$production->toArray());

		$acceptation = Atk14DeploymentStage::GetStage("acceptation");
		$this->_compareArrays(array(
			"name" => "acceptation",
			"user" => "deploy",
			"server" => "zeus.mushoomradar.net",
			"port" => null,
			"directory" => "/home/deploy/apps/mushoomradar_acc/",
			"deploy_via" => "git_push",
			"deploy_repository" => "deploy@zeus.mushoomradar.net:repos/mushoomradar_acc.git",
			"deploy_branch" => "master",
			"create_maintenance_file" => true,
			"before_deploy" => array("@local composer update", "@local grunt dist"),
			"rsync" => array(),
			"after_deploy" => array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),
		),$acceptation->toArray());

		$acceptation2 = Atk14DeploymentStage::GetStage("acceptation2");
		$this->_compareArrays(array(
			"name" => "acceptation2",
			"user" => "deploy",
			"server" => "zeus.mushoomradar.net",
			"port" => null,
			"directory" => "/home/deploy/apps/mushoomradar_acc2/",
			"deploy_via" => "git_push",
			"deploy_repository" => "deploy@zeus.mushoomradar.net:repos/mushoomradar_acc2.git",
			"deploy_branch" => "master",
			"create_maintenance_file" => true,
			"before_deploy" => array("@local composer update", "@local grunt dist"),
			"rsync" => array(),
			"after_deploy" => array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),
		),$acceptation2->toArray());

		// it is unable to set a value
		$exception_thrown = false;
		try{
			$devel->rsync = "bad_try";
			$this->fail();
		}catch(Exception $e){
			//
			$exception_thrown = true;
		}
		$this->assertEquals(true,$exception_thrown);
		$this->assertEquals(array("public/dist/","vendor/"),$devel->rsync);
	}

	function _compareArrays($exp_ar,$ar){
		foreach($exp_ar as $key => $exp){
			$this->assertEquals($exp,$ar[$key],$key);
		}
	}
}

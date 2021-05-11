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

		$this->assertEquals("devel",$devel->getName());
		$this->assertEquals("/home/deploy/apps/mushoomradar_devel/",$devel->getDirectory());
		$this->assertEquals(array("public/dist/","vendor","public/sitemap.xml"),$devel->getRsync());

		$acceptation = Atk14DeploymentStage::GetStage("acceptation");
		$this->assertEquals("acceptation",$acceptation->getName());
		$this->assertEquals("/home/deploy/apps/mushoomradar_acc/",$acceptation->getDirectory());
		$this->assertEquals(array(),$acceptation->getRsync());
		
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
			"rsync" => array("public/dist/","vendor","public/sitemap.xml"),
			"after_deploy" => array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),
		),$devel->toArray());

		$production = Atk14DeploymentStage::GetStage("production");
		$this->assertEquals("production",$production->name);
		$this->assertEquals("deployment_stage_production","$production");
		$this->_compareArrays(array(
			"name" => "production",
			"user" => "deploy",
			"server" => "zeus.mushoomradar.net",
			"port" => "2222",
			"env" => 'PATH=/home/deploy/bin:$PATH',
			"directory" => "/home/deploy/apps/mushoomradar_production/",
			"deploy_via" => "git_push",
			"deploy_repository" => "deploy@zeus.mushoomradar.net:repos/mushoomradar.git",
			"deploy_branch" => "master",
			"create_maintenance_file" => false,
			"before_deploy" => array("@local composer update", "@local grunt dist"),
			"rsync" => array("public/dist/","vendor","public/sitemap.xml"),
			"after_deploy" => array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),
		),$production->toArray());

		$acceptation = Atk14DeploymentStage::GetStage("acceptation");
		$this->_compareArrays(array(
			"name" => "acceptation",
			"user" => "deploy",
			"server" => "zeus.mushoomradar.net",
			"port" => null,
			"env" => "",
			"directory" => "/home/deploy/apps/mushoomradar_acc/",
			"deploy_via" => "git_push",
			"deploy_repository" => "deploy@zeus.mushoomradar.net:/home/deploy/repos/mushoomradar_acc.git",
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
			"env" => "",
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
		$this->assertEquals(array("public/dist/","vendor","public/sitemap.xml"),$devel->rsync);
	}

	function test_getDeployRepository(){
		// in config: "deploy@devel:repos/mushoomradar.git"
		$devel = Atk14DeploymentStage::GetStage("devel");
		$this->assertEquals("deploy@devel.mushoomradar.net:repos/mushoomradar.git",$devel->getDeployRepository());
		$this->assertEquals("/home/deploy/repos/mushoomradar.git",$devel->getDeployRepositoryRemoteDir());

		// in config: "deploy@zeus.mushoomradar.net:repos/mushoomradar.git"
		$production = Atk14DeploymentStage::GetStage("production");
		$this->assertEquals("deploy@zeus.mushoomradar.net:repos/mushoomradar.git",$production->getDeployRepository());
		$this->assertEquals("/home/deploy/repos/mushoomradar.git",$production->getDeployRepositoryRemoteDir());

		// in config: "/home/deploy/repos/mushoomradar_acc.git"
		$acceptation = Atk14DeploymentStage::GetStage("acceptation");
		$this->assertEquals("deploy@zeus.mushoomradar.net:/home/deploy/repos/mushoomradar_acc.git",$acceptation->getDeployRepository());
		$this->assertEquals("/home/deploy/repos/mushoomradar_acc.git",$acceptation->getDeployRepositoryRemoteDir());
	}

	function test_compileRemoteShellCommand(){
		$devel = Atk14DeploymentStage::GetStage("devel");
		$this->assertEquals('ssh deploy@devel.mushoomradar.net "cd /home/deploy/apps/mushoomradar_devel/ && export ATK14_ENV=production && (./scripts/migrate)"',$devel->compileRemoteShellCommand("./scripts/migrate"));
		$this->assertEquals('ssh deploy@devel.mushoomradar.net "cd /home/deploy/apps/mushoomradar_devel/ && export ATK14_ENV=production && (./scripts/delete_temporary_files \"dbmole_cache\")"',$devel->compileRemoteShellCommand('./scripts/delete_temporary_files "dbmole_cache"'));
		$this->assertEquals('ssh deploy@devel.mushoomradar.net "cd /home/deploy/apps/mushoomradar_devel/ && export ATK14_ENV=production && (./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache)"',$devel->compileRemoteShellCommand('./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache'));
		// changing directory to the project
		$this->assertEquals('ssh deploy@devel.mushoomradar.net "cd /home/deploy/apps/mushoomradar_devel/ && export ATK14_ENV=production && (id)"',$devel->compileRemoteShellCommand("id"));
		$this->assertEquals('ssh deploy@devel.mushoomradar.net "export ATK14_ENV=production && (id)"',$devel->compileRemoteShellCommand("id",false));
		$this->assertEquals('ssh deploy@devel.mushoomradar.net "cd /home/deploy/apps/mushoomradar_devel/ && export ATK14_ENV=production && (id)"',$devel->compileRemoteShellCommand("id",true));

		// a stage with port
		$production = Atk14DeploymentStage::GetStage("production");
		$this->assertEquals('ssh deploy@zeus.mushoomradar.net -p 2222 "cd /home/deploy/apps/mushoomradar_production/ && export ATK14_ENV=production PATH=/home/deploy/bin:$PATH && (./scripts/migrate)"',$production->compileRemoteShellCommand("./scripts/migrate"));
	}

	function test_compileRsyncCommand(){
		$devel = Atk14DeploymentStage::GetStage("devel");
		$this->assertEquals('rsync -av --checksum --no-times --delete public/dist/ deploy@devel.mushoomradar.net:/home/deploy/apps/mushoomradar_devel/public/dist/',$devel->compileRsyncCommand("public/dist/"));
		$this->assertEquals('rsync -av --checksum --no-times --delete vendor/ deploy@devel.mushoomradar.net:/home/deploy/apps/mushoomradar_devel/vendor/',$devel->compileRsyncCommand("vendor"));
		$this->assertEquals('rsync -av --checksum --no-times --delete public/dist/sitemap.xml deploy@devel.mushoomradar.net:/home/deploy/apps/mushoomradar_devel/public/dist/sitemap.xml',$devel->compileRsyncCommand("public/dist/sitemap.xml"));

		$production = Atk14DeploymentStage::GetStage("production");
		$this->assertEquals("rsync -av --checksum --no-times --delete -e 'ssh -p 2222' public/dist/ deploy@zeus.mushoomradar.net:/home/deploy/apps/mushoomradar_production/public/dist/",$production->compileRsyncCommand("public/dist/"));
		$this->assertEquals("rsync -av --checksum --no-times --delete -e 'ssh -p 2222' vendor/ deploy@zeus.mushoomradar.net:/home/deploy/apps/mushoomradar_production/vendor/",$production->compileRsyncCommand("vendor"));
	}

	function _compareArrays($exp_ar,$ar){
		foreach($exp_ar as $key => $exp){
			$this->assertEquals($exp,$ar[$key],$key);
			$this->assertTrue($exp===$ar[$key],$key);
		}
	}
}

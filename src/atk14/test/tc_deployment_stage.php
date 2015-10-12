<?php
class TcDeploymentStage extends TcBase{
	function test(){
		$stages = Atk14DeploymentStage::GetStages();

		$this->assertEquals(3,sizeof($stages));
		$this->assertEquals(array("devel","acceptation","production"),array_keys($stages));

		$first_stage = Atk14DeploymentStage::GetFirstStage();
		$this->assertEquals("devel",$first_stage->name);

		// non existing stage
		$this->assertEquals(null,Atk14DeploymentStage::GetStage("preview"));

		$devel = Atk14DeploymentStage::GetStage("devel");
		$this->assertEquals("devel",$devel->name);
		$this->assertEquals("deployment_stage_devel","$devel");
		$this->assertEquals("zeus.mushoomradar.net",$devel->server);
		$this->assertEquals("/home/deploy/apps/mushoomradar_devel/",$devel->directory);
		$this->assertEquals(true,$devel->create_maintenance_file);
		$this->assertEquals(array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),$devel->after_deploy);
		$this->assertEquals(array("public/dist/","vendor/"),$devel->rsync);

		$production = Atk14DeploymentStage::GetStage("production");
		$this->assertEquals("production",$production->name);
		$this->assertEquals("deployment_stage_production","$production");
		$this->assertEquals("zeus.mushoomradar.net",$production->server);
		$this->assertEquals("/home/deploy/apps/mushoomradar/",$production->directory);
		$this->assertEquals(false,$production->create_maintenance_file);
		$this->assertEquals(array("./scripts/migrate && ./scripts/delete_temporary_files dbmole_cache"),$production->after_deploy);
		$this->assertEquals(array("public/dist/","vendor/"),$production->rsync);

		$acceptation = Atk14DeploymentStage::GetStage("acceptation");
		$this->assertEquals(array(),$acceptation->rsync);
		$this->assertEquals(false,$acceptation->create_maintenance_file);

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
}

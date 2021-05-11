<?php
class TcMigration extends TcBase {

	function test_SetDatabaseSchema(){
		$schemas = $this->dbmole->selectRows("SELECT schema_name FROM information_schema.schemata");
		if(!in_array("test",$schemas)){
			$this->dbmole->doQuery("CREATE SCHEMA test");
		}
		
		$this->assertEquals("public",TableRecord_DatabaseAccessor_Postgresql::GetDefaultDatabaseSchema());

		Atk14Migration::SetDatabaseSchema("test");
		$this->assertEquals("test",TableRecord_DatabaseAccessor_Postgresql::GetDefaultDatabaseSchema());

		Atk14Migration::SetDatabaseSchema("public");
		$this->assertEquals("public",TableRecord_DatabaseAccessor_Postgresql::GetDefaultDatabaseSchema());
	}
}

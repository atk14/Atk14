<?php
class TcMigration extends TcBase {

	function test_SetDatabaseSchema(){
		$this->assertEquals("public",TableRecord_DatabaseAccessor_Postgresql::GetDefaultDatabaseSchema());

		Atk14Migration::SetDatabaseSchema("test");
		$this->assertEquals("test",TableRecord_DatabaseAccessor_Postgresql::GetDefaultDatabaseSchema());

		Atk14Migration::SetDatabaseSchema("public");
		$this->assertEquals("public",TableRecord_DatabaseAccessor_Postgresql::GetDefaultDatabaseSchema());
	}
}

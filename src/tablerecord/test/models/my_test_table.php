<?php
class MyTestTable extends TableRecord{
	function __construct(){
		parent::__construct(array(
			"table_name" => "test_table",
			"dbmole" => MysqlMole::GetInstance(),
		));
	}
}

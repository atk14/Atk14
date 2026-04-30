<?php
class TestTableAlternative extends TableRecord {

	function __construct(){
		parent::__construct([
			"table_name" => "test_table",
			"sequence_name" => "test_table_id_seq",
			"dbmole" => $GLOBALS["dbmole_alternative"],
		]);
	}
}

<?php
/**
 * A class for testing purposes
 */
class TestTable extends TableRecord{

	function __construct(){
		//parent::__construct("test_table",array("sequence_name" => "test_table_id_seq"));
		// or
		parent::__construct(array(
			"table_name" => "test_table",
			"sequence_name" => "test_table_id_seq",
		));
	}

}

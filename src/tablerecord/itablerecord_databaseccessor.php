<?php
interface iTableRecord_DatabaseAccessor {
	function setRecordValues($data_row,&$record_values,$record);
	function readTableStructure($record,$options);
}

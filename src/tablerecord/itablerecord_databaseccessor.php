<?php
interface iTableRecord_DatabaseAccessor {
	static function SetRecordValues($data_row,&$record_values,$record);
	static function ReadTableStructure($record,$options);
}

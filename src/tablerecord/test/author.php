<?php
class Author extends TableRecord{
	function __construct(){
		parent::__construct(array(
			"table_name" => "authors",
		));
	}
}

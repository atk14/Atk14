<?php
class Author extends TableRecord{
	function __construct(){
		parent::__construct([
			"table_name" => "authors",
		]);
	}
}

<?php
class AuthorWithSchema extends TableRecord {
	function __construct(){
		parent::__construct("public.authors");
	}
}

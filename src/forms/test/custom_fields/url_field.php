<?php
/*
 * Field accepts URL like
 * 	* http://www.domain.com/
 * 	* https://www.domain.com/
 * 	* www.domain.com
 */
class UrlField extends RegexField{
	function __construct($options = array()){
		parent::__construct('/^(https?:\/\/|)[a-z0-9.-]+(|:[0-9]{1,6})(\/.*|)$/i',$options);
		$this->update_messages(array(
			"invalid" => _("This doesn't look like an URL"),
		));
	}

	function processResult($value, $matches){
		if($matches[1]==""){
			$value = "http://$value";
		}
		if($matches[3]==""){
			$value = "$value/";
		}
		return array(null, $value);
	}
}

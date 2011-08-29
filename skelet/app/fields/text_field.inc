<?php
/**
 * TextField renders <textarea> HTML tag
 * and preserves white spaces by default.
 */
class TextField extends CharField{
	function __construct($options = array()){
		$options = array_merge(array(
			"widget" => new TextArea(),
			"trim_value" => false,
		),$options);
		parent::__construct($options);
	}
}

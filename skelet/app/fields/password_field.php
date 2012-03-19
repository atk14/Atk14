<?php
/**
 * Such an important field for nearly every web app and it is missing in the Form package...!?
 * Actually there is a good oportunity to demostrate a new field creation. So take a look.
 * 
 * PasswordField preserves spaces at the begining or at the end. It's really notable because
 * a strong enough password contains mainly spaces :)
 */
class PasswordField extends CharField{
	function __construct($options = array()){
		$options = array_merge(array(
			"widget" => new PasswordInput(),
			"null_empty_output" => true,
			"trim_value" => false,
		),$options);

		parent::__construct($options);
	}
}

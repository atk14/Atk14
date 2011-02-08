<?
/**
* Such a important field for nearly every web app and is it missing in the Form package?
* Actually there is a good reason to demostrate a new field creation. So take a look.
* 
* PasswordField preserves spaces at the begining or at the end. It's really notable because
* a password strong enough contains mainly spaces :)
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

<?php
/**
 * Input field for entering a password
 *
 * Be aware of the fact that the PasswordField is considering an initial value.
 * Sometimes you may not want to send a password to HTML. See the following example.
 *
 *		// $form has a PasswordField named password: $form->add_field("password", new PasswordField());
 *		$user = User::FindById(123);
 *		$initial = $user->toArray();
 *		unset($initial["password"]);
 *		$form->set_initial($initial);
 */
class PasswordField extends CharField{
	function __construct($options = array()){
		$options = array_merge(array(
			"widget" => new PasswordInput(array(
				"attrs" => array("class" => "form-control")
			)),
			"null_empty_output" => true,
	
			// set this option to false if you want PasswordField which accepts passwords with leading or trailing white chars
			// "trim_value" => false,
		),$options);

		parent::__construct($options);
	}
}

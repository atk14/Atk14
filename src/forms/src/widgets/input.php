<?php
/**
 * Base class for most widgets.
 *
 * Most <input> fields use this class.
 *
 * @package Atk14
 * @subpackage Forms
 */
class Input extends Widget
{
	var $input_type = null; // toto musi definovat konkretni odvozene tridy

	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	function __construct($options = array()){
		parent::__construct($options);
	}

	function render($name, $value, $options=array())
	{
		if(is_bool($value)){ $value = (int)$value;}
		settype($value,"string");

		$options = forms_array_merge(array('attrs'=> null), $options);

		$final_attrs = $this->build_attrs(array(
			'type' => $this->input_type, 
			'name' => $name),
			$options['attrs']
		);
		if (strlen($value)>0) {
			$final_attrs['value'] = $value;
		}
		return '<input'.flatatt($final_attrs).' />';
	}
}

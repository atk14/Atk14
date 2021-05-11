<?php
/**
 * Widgets -- HTML representation of form fields.
 *
 *
 * Each field has its own code that is rendered in HTML.
 * For example {@link CharField} is rendered as <input type="text" />
 *
 * The way the field is rendered can be changed.
 * For example a field for date can be rendered both as a text field and also as a select with 3 options.
 * In both cases they are the same field that return the same value whatever it is rendered.
 *
 * <code>
 * $this->add_field("choice", new ChoiceField(array(
 *  "label" => "Your choice",
 *  "required" => true,
 *  "choices" => array(
 *    "" => "Decide later",
 *    "yes" => "Yes",
 *    "no" => "Absolutely not",
 *    ),
 *  "widget" => new RadioSelect(),
 *  )));
 *
 * </code>
 *
 * @filesource
 */

/**
 * Parent class for all widget types.
 *
 * This class shouldn't be used directly but through its descendant.
 *
 * @package Atk14\Forms
 */
class Widget
{
	/**
	 * Is multipart encoding required for form submission?
	 */
	var $multipart_encoding_required = false;

	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	function __construct($options=array())
	{
		$options = forms_array_merge(array('attrs'=>null), $options);
		if (!isset($this->is_hidden)) {
			$this->is_hidden = false;
		}
		if (is_null($options['attrs'])) {
			$this->attrs = array();
		}
		else {
			$this->attrs = $options['attrs'];
		}
	}

	/**
	 * Renders widget as a HTML element.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param array $attrs
	 * @return string HTML code of the element
	 * @abstract
	 */
	function render($name, $value, $attrs)
	{
		return ''; // NOTE: Django v tomto miste generuje vyjimku (ktera vyvola tusim chybu 50x)
	}

	/**
	 * Completes all attributes for a widget.
	 */
	function build_attrs($attrs, $extra_attrs=array())
	{
		return forms_array_merge($this->attrs, $attrs, $extra_attrs);
	}

	/**
	* Vrati hodnotu widgetu.
	*/
	function value_from_datadict($data, $name)
	{
		if (isset($data[$name])) {
			return $data[$name];
		}
		else {
			return null;
		}
	}

	/**
	* Vraci atribut id HTML prvku (pouziva se pro <label>).
	*/
	function id_for_label($id_)
	{
		return $id_;
	}
}

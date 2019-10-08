<?php
/**
 * Formulare.
 *
 * @package Atk14
 * @subpackage Forms
 * @filesource
 *
 */

/**
 * Some constant definition
 *
 * @todo Write better explanation
 */
defined('NON_FIELD_ERRORS') || define('NON_FIELD_ERRORS', '__all__');

/**
 * Class managing javascript validators for each input field
 *
 * Uses jQuery validate plugin
 */
class JsFormValidator{

	/**
	 * Set of validation rules for each input field.
	 *
	 * @var array
	 */
	var $validators = array();

	function __construct(&$form){
		$this->validators = array();
		$this->_fields_html_names = array();
		foreach($form->fields as $name => $field){
			$this->validators[$name] = $field->js_validator();
			$bf = new BoundField($form, $field, $name);
			$this->_fields_html_names[$name] = $bf->html_name;
		}
	}

	/**
	 * print_r($js_validator->get_rules());
	 * print_r($js_validator->get_rules("phone"));
	 */
	function get_rules($field = null){
		$out = array();
		foreach($this->validators as $name => $validator){
			if($rules = $validator->get_rules()){
				$key = $this->_fields_html_names[$name];
				$out[$key] = $rules;
			}
		}

		if($field){
			return isset($out[$field]) ? $out[$field] : array();
		}

		return $out;
	}

	/**
	 * print_r($js_validator->get_messages());
	 * print_r($js_validator->get_messages("phone"));
	 */
	function get_messages($field = null){
		$out = array();
		foreach($this->validators as $name => $validator){
			if($msgs = $validator->get_messages()){
				$key = $this->_fields_html_names[$name];
				$out[$key] = $msgs;
			}
		}

		if($field){
			return isset($out[$field]) ? $out[$field] : array();
		}

		return $out;
	}
}

/**
 * Bound field je trida pro formularove pole (field), do ktereho jsou nalite data.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class BoundField {
	/**
	 * @var Field
	 */
	var $field;

	/**
	 * @var Widget
	 */
	var $widget;

	function __construct($form, $field, $name)
	{
		$this->form = $form;
		$this->field = $field;
		$this->widget = $field->widget;
		$this->name = $name;
		$this->html_name = $form->add_prefix($name);
		if (is_null($this->field->label)) {
			$this->label = pretty_name($name);
		}
		else {
			$this->label = $this->field->label;
		}
		$this->help_text = $field->help_text;
		$this->hint = $field->hint;
		$this->hints = $field->hints;
		$this->required = $this->field->required;
		$this->disabled = $this->field->disabled;
	}

	/**
	* NOTE: v djangu realizovano jako property na cteni 'errors'
	*/
	function errors()
	{
		if (isset($this->form->errors[$this->name])) {
			return $this->form->errors[$this->name];
		}
		else {
			return array();
		}
	}

	function is_bound(){
		return $this->form->is_bound;
	}

	/**
	* Vykresli pole (field), budto s pomoci zadaneho widgetu,
	* nebo s pomoci defaultniho widgetu definovaneho u field.
	*/
	function as_widget($options=array())
	{
		$options = forms_array_merge(
			array(
				'widget' => null, 
				'attrs'  => null
			),
			$options
		);
		if (!$options['widget']) {
			$widget = $this->field->widget;
		}
		else {
			$widget = $options['widget'];
		}
		if ($options['attrs']) {
			$attrs = $options['attrs'];
		}
		else {
			$attrs = array();
		}
		$auto_id = $this->auto_id();

		if ($auto_id && !isset($attrs['id']) && !isset($widget->attrs['id'])) {
			$attrs['id'] = $auto_id;
		}
		if($this->field->disabled){
			$attrs['disabled'] = 'disabled';
		}
		if (!$this->form->is_bound || $this->field->disabled) {
			if (isset($this->form->initial[$this->name])) {
				$data = $this->field->format_initial_data($this->form->initial[$this->name]);
			}
			else {
				$data = $this->field->format_initial_data($this->field->initial);
			}
		}
		else {
			$data = $this->data();
		}

		return $widget->render($this->html_name, $data, array('attrs'=>$attrs));
	}

	/**
	* Vrati pole jako <input type="text" />.
	*/
	function as_text($attrs=null)
	{
		return $this->as_widget(array(
			'widget' => new TextInput(),
			'attrs' => $attrs
		));
	}

	/**
	* Vrati pole jako <textarea></textarea>.
	*/
	function as_textarea($attrs=null)
	{
		return $this->as_widget(array(
			'widget' => new TextArea(),
			'attrs' => $attrs
		));
	}

	/**
	* Vrati pole jako <input type="hidden" />.
	*/
	function as_hidden($attrs=null)
	{
		return $this->as_widget(array(
			'widget' => $this->field->hidden_widget,
			'attrs' => $attrs
		));
	}

	/**
	* NOTE: pri cteni property 'data' se v Djangu vola nasledujici funkce
	*/
	function data()
	{
		return $this->field->widget->value_from_datadict(
			$this->form->data,
			$this->html_name
		);
	}

	/**
	* Pokud ma field definovan atribut 'id', obali se zadany 
	* 'contents' v tagu <label> (na content se nevola htmlspecialchars).
	* Pokud 'content' zadan neni, pouzije se hodnota z $field->label
	* (escapeovana s pomoci htmlspecialchars).
	*
	* Pokud jsou v $options definovany atributy 'attrs', nalijou se
	* do tagu <label>.
	*/
	function label_tag($options=array())
	{
		$options = forms_array_merge(
			array(
				'contents' => null, 
				'attrs'  => null
			),
			$options
		);

		if ($options['contents']) {
			$contents = $options['contents'];
		}
		else {
			$contents = forms_htmlspecialchars($this->label);
		}
		if ($id_ = $this->get_id()) {
			if (is_array($options) && is_array($options['attrs']) && (count($options['attrs']) > 0)) {
				$attrs = flatatt($options['attrs']);
			}
			else {
				$attrs = '';
			}
			$widget = $this->field->widget;
			$contents = '<label for="'.$widget->id_for_label($id_).'"'.$attrs.'>'.$contents.'</label>';
		}
		return $contents;
	}

	function get_id()
	{
		$widget = $this->field->widget;
		if (isset($widget->attrs['id'])) {
			 $id_ = $widget->attrs['id'];
		} else {
			 $id_ = $this->auto_id();
		}
		return $id_;
	}

	function id_for_label(){ return $this->field->widget->id_for_label($this->get_id()); }

	/**
	 * Checks if the used widget is of class HiddenInput.
	 *
	 * @internal tohle je v Djangu property
	 * @return bool
	 */
	function is_hidden()
	{
		return $this->field->widget->is_hidden;
	}

	/**
	 * Pokud ma formular definovan parametr auto_id, vrati tato metoda
	 * vypocitany atribut ID pro formularove pole.
	 * NOTE: tohle je Djangu property
	 *
	 * @return string
	 */
	function auto_id() {
		$auto_id = $this->form->auto_id;
		if (strpos($auto_id, '%s') !== false) {
			return str_replace('%s', $this->html_name, $auto_id);
		}
		elseif ($auto_id) {
			return $this->html_name;
		}
		return '';
	}

	/**
	 * Returns initial value of the field.
	 *
	 * @return mixed
	 */
	function get_initial()
	{
		if(isset($this->form->initial[$this->name])){ return $this->form->initial[$this->name]; }
		if(isset($this->field->initial)){ return $this->field->initial; }
		return null;
	}

	/**
	 * Magic method for conversion to string
	 *
	 * ```
	 * echo "$field";
	 * ```
	 * should render
	 * ```
	 * <input type="text" name="company" id="id_company" />
	 * ```
	 * @return string
	 */
	function __toString(){
		return $this->as_widget();
	}
}


/**
 * Basic class for managing forms.
 *
 * You should use the class {@link Atk14Form} instead of this class in an application.
 *
 * @todo Write about creating and using forms
 * @package Atk14
 * @subpackage Forms
 */
class Form implements ArrayAccess
{
	/**
	 * @var string
	 * @access private
	 * @todo make the attribute private and fix tests
	 */
	var $prefix = null;

	/**
	 * @var string
	 */
	private $label_suffix = "";

	/**
	 * Format of attribute 'id' used by inputs nested in a form.
	 *
	 * @see Form::__construct()
	 * @var string
	 * @access private
	 * @todo make the attribute private and fix tests
	 */
	var $auto_id = "";

	/**
	 * @var bool
	 * @access private
	 * @todo make the attribute private and fix tests
	 */
	var $is_bound;

	/**
	 * Data submitted in the form.
	 *
	 * @var array
	 * @access private
	 */
	var $data;


	/**
	 * Cleaned data.
	 *
	 * Array with submitted data. Each element contains value processed by {@link Field::clean()} method. The entry must be valid to appear in $cleaned_data.
	 *
	 * @var array
	 */
	var $cleaned_data;

	/**
	 * Initial values of the form.
	 *
	 * @var array
	 * @access private
	 */
	var $initial = null;

	/**
	 * Array of fields
	 *
	 * @var array
	 * @access private
	 */
	var $fields = array();

	/**
	 * Array with error messages.
	 *
	 * @var array
	 * @access private
	 */
	var $errors = null;

	/**
	 *
	 * @param array $options
	 * - prefix [default null]
	 * - label_suffix [default ':']
	 * - auto_id affects generation of 'id' attribute of nested inputs [default: 'id_%s']
	 * 	- false - inputs will not have 'id' attribute. Nor label attributes will be generated.
	 * 	- true - attribute id will be generated and it will equal to the name attribute
	 * 	- string
	 * 		- in case of common string it will be evaluated as true and the id attribute will be generated as in that case
	 * 		- when the string contains '%s' the resulting name will be generated by replacing the '%s' by the name of the input field
	 */
	function __construct($options=array())
	{
		$options = forms_array_merge(
			array(
				'call_set_up'   => true,
				'__do_small_initialization' => true, // don't even think about it :)
				'__do_big_initialization' => true, // do not touch this :)
			),
			$options
		);
		$options["__do_small_initialization"] && $this->__do_small_initialization($options);
		$options["__do_big_initialization"] && $this->__do_big_initialization($options);
		$options["call_set_up"] && $this->set_up();
	}

	function __do_small_initialization($options = array()){
		$options = forms_array_merge(
			array(
				'auto_id'       => 'id_%s',
				'prefix'        => null, 
				'label_suffix'  => ':',
			),
			$options
		);

		$this->auto_id = $options['auto_id'];
		$this->prefix = $options['prefix'];
		$this->label_suffix = $options['label_suffix'];

		$this->initial = array();
		$this->errors = null;
		$this->fields = array();
	}

	function __do_big_initialization($options = array()){
		$options = forms_array_merge(
			array(
				'data'          => null, 
				'initial'       => null, 
			),
			$options
		);

		$this->is_bound = !is_null($options['data']);
		if (is_array($options['data'])) {
			$this->errors = null;
			$this->data = $options['data']; 
		}
		else {
			$this->data = array();
		}
		if (is_array($options['initial'])) {
			$this->initial = $options['initial'];
			foreach($options["initial"] as $k => $v){
				$k = (string)$k;
				if(isset($this->fields[$k])){
					$this->fields[$k]->initial = $v;
				}
			}
		}
	}

	/**
	 * Initialization method.
	 *
	 * This abstract method is used in descendants to setup an instance of a form and to define its fields.
	 *
	 * NOTE: muj vymysl
	 * @abstract
	 */
	function set_up()
	{
	}

	/**
	 * Set prefix for forms input names.
	 *
	 * This method is intended for using when it can not be set in constructor
	 *
	 * @param string $prefix
	 */
	function set_prefix($prefix) {
		$this->prefix = $prefix;
	}

	/**
	 * Adds a field to the form.
	 *
	 * @param string $name
	 * @param Field $field
	 *
	 * @return Field
	 * @internal muj vymysl
	 */
	function add_field($name, $field)
	{
		$this->fields[$name] = $field;
		return $field;
	}

	/**
	 * Returns a field object by its name.
	 *
	 * @param string $name name of a field
	 * @return Field
	 * @internal Michaluv vymysl
	 */
	function get_field($name)
	{
		if (isset($this->fields[$name])) {
			return new BoundField($this, $this->fields[$name], $name);
		}
		else {
			return null;
		}
	}

	/**
	 * Returns names of form fields
	 *
	 * @return array
	 *
	 * @internal NOTE: Michaluv vymysl
	 */
	function get_field_keys()
	{
		return array_keys($this->fields);
	}

	/**
	 * Returns list of fields.
	 *
	 * @param string $wildcart
	 * @return array of strings with field names
	 * @note what about the param
	 */
	function list_fields($wildcart = ""){
		$out = $this->get_field_keys();
		return $out;
	}

	/**
	 * Checks if the form requires multipart emcoding.
	 *
	 * @return bool
	 */
	function is_multipart(){
		foreach($this->fields as $f){
			if($f->widget->multipart_encoding_required){ return true; }
		}
		return false;
	}

	/**
	 * Returns initial value of a field.
	 *
	 * @param string $name
	 * @return mixed
	 */
	function get_initial($name)
	{
		if(isset($this->initial[$name])){ return $this->initial[$name]; }
		return $this->fields[$name]->initial;
	}

	/**
	 * Returns array with form error messages.
	 *
	 * @return array
	 * @internal NOTE: muj vymysl, Django to ma zrealizovane pres property
	 */
	function get_errors()
	{
		if (is_null($this->errors)) {
			$this->full_clean();
		}
		return $this->errors;
	}

	/**
	 * Checks for form validity.
	 *
	 * Returns true when the form is valid, otherwise returns false.
	 * @return bool
	 */
	function is_valid()
	{
		$errors = $this->get_errors();
		return ($this->is_bound && (count($errors) < 1));
	}

	/**
	 * Checks for error on a field.
	 *
	 * Returns true when there is an error on specified field.
	 *
	 * ```
	 * if($form->error_on("email")){
	 * 	 echo "mate chybu na policku E-mail";
	 * }
	 * ```
	 *
	 * @param string $name
	 * @return bool true if there is an error on specified field.
	 */
	function error_on($field_name) {
		$errors = $this->get_errors();
		return ($this->is_bound && isset($errors[$field_name]) && sizeof($errors[$field_name])>0);
	}

	/**
	 * Returns name of a field including prefix when it is set.
	 *
	 * Prefix can be set by {@link Form::set_prefix()}
	 *
	 * @param string $field_name
	 * @return string
	 */
	function add_prefix($field_name) {
		if ($this->prefix) {
			return $this->prefix.'_'.$field_name;
		}
		else {
			return $field_name;
		}
	}

	/**
	 * Disables specified fields.
	 *
	 * Sets $disabled flag of specified fields.
	 *
	 * ```
	 * $form->disable_fields(array(
	 * 	"firstname",
	 * 	"lastname"
	 * ));
	 * ```
	 *
	 * @param array $names
	 */
	function disable_fields($names){
		foreach($names as $name){
			if(!isset($this->fields[$name])){ continue; }
			$this->fields[$name]->disabled = true;
		}
	}

	/**
	 * Helper method that generates form as HTML.
	 *
	 * @access private
	 * @return string
	 */
	function _html_output($normal_row, $error_row, $row_ender, $help_text_html, $errors_on_separate_row)
	{
		$top_errors = $this->non_field_errors();
		$output = array();
		$hidden_fields = array();

		foreach ($this->fields as $name => $field) {
			$bf = new BoundField($this, $field, $name);
			$bf_errors = array();
			foreach ($bf->errors() as $_bf) {
				$bf_errors[] = forms_htmlspecialchars($_bf);
			}
			if ($bf->is_hidden()) {
				if ($bf_errors) {
					foreach ($bf_errors as $e) {
						$top_errors[] = '(Hidden field '.$name.') '.$e;
					}
				}
				$hidden_fields[] = $bf->as_widget();
			}
			else {
				if ($errors_on_separate_row && $bf_errors) {
					$output[] = str_replace('%s', format_errors($bf_errors), $error_row);
				}
				if ($bf->label) {
					$label = forms_htmlspecialchars($bf->label);
					if ($this->label_suffix) {
						if (!in_array($label[strlen($label)-1], array(':', '?', '.', '!'))) {
							$label .= $this->label_suffix;
						}
					}
					$label = $bf->label_tag(array('contents'=>$label));
					if (!$label) {
						$label = '';
					}
				}
				else {
					$label = '';
				}
				if ($field->help_text) {
					$help_text = str_replace('%s', $field->help_text, $help_text_html);
				}
				else {
					$help_text = '';
				}
				if (is_array($bf_errors)) {
					$bf_errors = format_errors($bf_errors);
				}
				$output[] = EasyReplace(
					$normal_row, 
					array(
						'%(label)s' => $label,
						'%(errors)s' => $bf_errors,
						'%(field)s' => $bf->as_widget(),
						'%(help_text)s' => $help_text,
					)
				);
			}
		}
		if (count($top_errors) > 0) {
			$output = forms_array_merge(str_replace('%s', format_errors($top_errors), $error_row), $output); 
		}
		// Insert any hidden fields in the last row.
		if ($hidden_fields) { 
			$str_hidden = implode('', $hidden_fields);
			if ($output) {
				$last_row = $output[count($output)-1];
				// Chop off the trailing row_ender (e.g. '</td></tr>') and
				// insert the hidden fields.
				$output[count($output)-1] = substr($last_row, 0, strlen($last_row)-strlen($row_ender)).$str_hidden.$row_ender;
			}
			else {
				// If there aren't any rows in the output, just append the
				// hidden fields.
				$output[] = $str_hidden;
			}
		}
		return implode("\n", $output);
	}

	/**
	 * Renders the form as HTML table.
	 *
	 * @return string The form presented as HTML code.
	 */
	function as_table()
	{
		return $this->_html_output(
			'<tr><th>%(label)s</th><td>%(errors)s%(field)s%(help_text)s</td></tr>',
			'<tr><td colspan="2">%s</td></tr>',
			'</td></tr>',
			'<br />%s',
			False
		);
	}

	/**
	 * Renders the form in <li> tags.
	 *
	 * Renders the form so that each field is enclosed in <li></li> tags. All the fields are then wrapped with <ul></ul> tags.
	 *
	 * @return string The form presented as HTML code.
	 */
	function as_ul()
	{
		return $this->_html_output(
			'<li>%(errors)s%(label)s %(field)s%(help_text)s</li>',
			'<li>%s</li>',
			'</li>',
			' %s',
			False
		);
	}

	/**
	 * Renders the form in <p> tags.
	 *
	 * Renders the form so that each field is enclosed in <p></p> tags.
	 *
	 * @return string The form presented as HTML code.
	 */
	function as_p()
	{
		return $this->_html_output(
			'<p>%(label)s %(field)s%(help_text)s</p>',
			'%s',
			'</p>',
			' %s',
			True
		);
	}

	/**
	 * Returns errors bound with the form.
	 *
	 * Errors that are bound to fields are omitted.
	 *
	 * @return array
	 */
	function non_field_errors()
	{
		$errors = $this->get_errors();
		if (isset($errors[NON_FIELD_ERRORS])) {
			return $errors[NON_FIELD_ERRORS];
		}
		else {
			return array();
		}
	}

	/**
	* Zkontroluje vsechny prvky formulare a vytvori z nich pole $this->errors a 
	* $this->cleaned_data.
	*/
	function full_clean()
	{
		$this->errors = array();
		if (!$this->is_bound) {
			return;
		}
		$this->cleaned_data = array();
		// pro kazde policko formulare zavolame clean metody
		foreach (array_keys($this->fields) as $name) {
			$field = &$this->fields[$name];
			// pokud je toto policko disablovane, bude v cleaned_data vracena initial hodnota
			// podle nebude validovano...
			if($field->disabled){
				$this->cleaned_data[$name] = $this->get_initial($name);
				continue;
			}
			$value = $field->widget->value_from_datadict(
				$this->data, 
				$this->add_prefix($name)
			);

			list($error, $value) = $field->clean($value);
			if(is_array($error) && sizeof($error)==0){ $error = null; }
			if (is_null($error)) {
				$this->cleaned_data[$name] = $value;
				if (method_exists($this, 'clean_'.$name)) {
					list($error, $value) = call_user_func(array($this, 'clean_'.$name));
					if (is_null($error)) {
						$this->cleaned_data[$name] = $value;
					}
				}
			}
			if (!is_null($error)) {
				if(!isset($this->errors[$name])){ $this->errors[$name] = array(); }
				if(is_array($error)){
					foreach($error as $e){ $this->errors[$name][] = (string)$e; }
				}else{
					$this->errors[$name][] = (string)$error;
				}
				if (isset($this->cleaned_data[$name])) {
					unset($this->cleaned_data[$name]);
				}
			}
		}
		// globalni clean pro cely formular
		list($error, $this->cleaned_data) = $this->clean();
		if (!is_null($error)) {
			if (isset($this->errors[NON_FIELD_ERRORS])) {
				$this->errors[NON_FIELD_ERRORS][] = $error;
			}
			else {
				$this->errors[NON_FIELD_ERRORS] = array($error);
			}
		}
		// pokud se vyskytla nejaka chyba, pak musime dat pryc cele pole cleaned_data
		if (count($this->errors) > 0) {
			unset($this->cleaned_data);
		}
	}

	/**
	 *
	 * Checks validity of the whole form.
	 *
	 * It is called after clean() on all fields was called.
	 * This is a hook method for situations when it is needed to check the form as a whole.
	 *
	 * Is called even when some fields are invalid.
	 *
	 * @return array
	 */
	function clean()
	{
		return array(null, $this->cleaned_data);
	}

	function js_validator(){
		return new JsFormValidator($this);
	}

	// The ArrayAccess interface functions
	/**
	 * @ignore
	 */
	function offsetExists($offset){ return isset($this->fields[$offset]); }

	/**
	 * @ignore
	 */
	function offsetGet($offset){ return $this->get_field($offset); }

	/**
	 * @ignore
	 */
	function offsetSet($offset,$value){
		if(!$value){
			// $form["firstname"] = null;
			unset($this[$offset]);
			return;
		}
		$this->add_field($offset,$value);
	}

	/**
	 * @ignore
	 */
	function offsetUnset($offset){ unset($this->fields[$offset]); }
}

<?php
/**
 * Class for advanced operations with forms.
 * @package Atk14
 * @subpackage Core
 * @filesource
 * @author Jaromir Tomek
 */

/**
 * Class for advanced operations with forms.
 *
 * This class is extension of basic class Form of the form framework.
 *
 * Atk14Form adds following features:
 * <ul>
 * 	<li>possibility to setup initial parameters of form elements later than in constructor.</li>
 * 	<li>possibility to setup sent user data later than in constructor</li>
 * 	<li>simple addition of hidden values</li>
 * 	<li>setup of action attribute of the form</li>
 * 	<li>methods begin() and end() for rendering of the form to template</li>
 * 	<li>method set_method() to change the value of forms method attribute</li>
 * </ul>
 *
 * Example of use:
 * <code>
 * class LoginForm extends Atk14Form{
 * 	function set_up(){
 * 		$this->add_field("login",new CharField(array(
 * 		"label" => _("Login"),
 * 		"help_text" => _("client identification"),
 * 		"min_length" => 1,
 * 		"max_length" => 64
 * 	)));
 *
 *	$this->add_field("password",new CharField(array(
 *		"label" => _("Password"),
 *		"min_length" => 1,
 *		"max_length" => 64
 *	)));
 *  	}
 *  }
 *
 * $form = new LoginForm();
 * $form->set_initial("login","user.name");
 * $form->set_hidden_field("action","login");
 * if($request->Post() && $form->is_valid($this->params)){
 *     // data jsou ok
 *     $data = $form->cleaned_data;
 * }
 * </code>
 *
 * Basically you can print a form by using helper {form} in a template:
 * <code>
 * {form}
 * {render partial=shared/form_field field=last_name}
 * {/form}
 * </code>
 *
 * which you can replace with this code (this actually uses {form} helper}:
 * <code>
 * {$form->begin()}
 * </code>
 * prints out beginning of a <form> element
 *
 * <code>
 * {$form->end()}
 * </code>
 * prints out closing tag of <form> element
 * with all hidden fields inside added by $form->set_hidden_field() method inside.
 *
 * <code>
 * {$form->get_error_report()}
 * </code>
 * prints out error summary.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 */
class Atk14Form extends Form
{
	/**
	 * Instance of controller using current form
	 * @var Atk14Controller
	 */
	var $controller = null;

	/**
	 * Target URL
	 *
	 * attribute action of the <form /> element
	 *
	 * @access private
	 * @var string
	 */
	var $atk14_action = "";

	/**
	 * Data sent by user.
	 *
	 * @access private
	 * @var array
	 */
	var $atk14_data = null;

	/**
	 * Array with hidden fields.
	 *
	 * @access private
	 * @var array
	 *
	 */
	var $atk14_hidden_fields = array();

	/**
	 * Array with list of form attributes
	 *
	 * @var array
	 * @access private
	 */
	var $atk14_attrs = array();

	/**
	 * @access private
	 */
	var $atk14_initial_values = null; // vychozi hodnoty; pokud se nenastavuje, musi byt null....

	/**
	 * @access private
	 */
	var $atk14_constructor_options = array();

	/**
	 * @access private
	 */
	var $atk14_super_constructor_called = false;

	/**
	 * @access private
	 */
	var $atk14_error_title = "";

	/**
	 * @access private
	 */
	var $atk14_errors = array();

	/**
	 * @access private
	 * @var string
	 */
	var $atk14_method = "post";

	/**
	 * @var bool
	 */
	var $atk14_csrf_protection_enabled = false;

	/**
	 * @param array $options valid options:
	 * <ul>
	 * 	<li><b>call_set_up</b></li>
	 * </ul>
	 * @param Atk14Controller $controller
	 *
	 * @todo complete options
	 */
	function Atk14Form($options = array(),$controller = null)
	{
		global $HTTP_REQUEST;

		$class_name = new String(get_class($this));

		$options = array_merge(array(
			"call_set_up" => true,
			"attrs" => array(),
		),$options);

		$this->atk14_attrs = $options["attrs"];

		$this->atk14_constructor_options = $options;

		$this->atk14_action = str_replace("&","&amp;",$HTTP_REQUEST->getRequestURI());
		//$this->atk14_action = Atk14Url::BuildLink(array()); // tohle sestavi URL s akt. $lang, $namespace, $controller a $action

		$this->controller = $controller;

		$this->atk14_error_title = _("The form contains errors, therefor it cannot be processed.");

		$this->_initialize();

		$options["call_set_up"] && $this->set_up();
	}

	/**
	 * Will be called before set_up().
	 * Just do inside anything you would like to.
	 */
	function _initialize(){ }

	/**
	 * Creates instance of Atk14Form by filename.
	 *
	 * File with given name contains definition of form class. You have sevaral ways to specify the filename.
	 *
	 * <ol>
	 * <li>With directory name, then the file is expected in current namespace directory.
	 * <code>
	 * $form = Atk14Form::GetInstanceByFilename("login/login_form.inc");
	 * </code>
	 * </li>
	 * <li>
	 * Without directory name, then the file is expected in current controller directory.
	 * <code>
	 * $form = Atk14Form::GetInstanceByFilename("login_form");
	 * </code>
	 * </li>
	 * </ol>
	 *
	 * You don't have to specify the .inc suffix. It will be added automatically.
	 * <code>
	 * $form = Atk14Form::GetInstanceByFilename("login/login_form");
	 * </code>
	 *
	 * @param string $filename name of file containing definition of {@link Atk14Form}
	 * @param Atk14Controller $controller_obj instance of {@link Atk14Controller} using this form
	 * @return Atk14Form instance of {@link Atk14Form}
	 *
	 * @static
	 */
	static function GetInstanceByFilename($filename,$controller_obj = null,$options = array())
	{
		global $ATK14_GLOBAL;
		$filename = preg_replace('/\.(inc|php)$/','',$filename); // nechceme tam koncovku

		$controller = $ATK14_GLOBAL->getValue("controller");
		$namespace = $ATK14_GLOBAL->getValue("namespace");
		$path = $ATK14_GLOBAL->getApplicationPath()."forms";

		// toto je preferovane poradi ve vyhledavani souboru s formularem
		$files = array(
		   "$path/$namespace/$filename",
		   "$path/$namespace/$controller/$filename",
		   "$path/$filename",
		   "$filename",
		);
		foreach($files as $_file){
			foreach(array("php","inc") as $suffix){
				if(file_exists("$_file.$suffix")){
					$filename = "$_file.$suffix";
					break;
				}
			}
		}
		$classname = "";
		if(preg_match('/([^\\/]+)_form\.(inc|php)$/',$filename,$matches)){
			$classname = "{$matches[1]}Form";
			$classname = str_replace("_","",$classname);
		}
		if(strlen($classname)==0 || !file_exists($filename)){
			return null;
		}
		require_once($filename);
		$form = new $classname($options,$controller_obj);
		return $form;
	}

	/**
	 * Get instance of a form by controller name and action name.
	 *
	 * <code>
	 * $form = Atk14Form::GetInstanceByControllerAndAction("login","login");
	 * </code>
	 *
	 * @param string $controller name of controller
	 * @param string $action name of action
	 * @param Atk14Controller $controller_obj instance of controller using the form (optional)
	 * @return Atk14Form instance of {@link Atk14Form}
	 * @static
	 *
	 */
	static function GetInstanceByControllerAndAction($controller,$action,$controller_obj = null,$options = array())
	{
		$options = array_merge(array(
			"attrs" => array(),
		),$options);
		$options["attrs"] = array_merge(array(
			"id" => "form_{$controller}_{$action}",
		),$options["attrs"]);

		($form = Atk14Form::GetInstanceByFilename("$controller/{$controller}_{$action}_form.inc",$controller_obj,$options)) || 
		($form = Atk14Form::GetInstanceByFilename("$controller/{$action}_form.inc",$controller_obj,$options));
		return $form;
	}

	/**
	 * Gets instance of a form by controller.
	 *
	 * Returns instance of a form by controller instance.
	 * Given the controller instance the method uses its name and name of action currently executed to get the correct form class name.
	 *
	 * @param Atk14Controller $controller
	 * @return Atk14Form instance of {@link Atk14Form}
	 * @uses GetInstanceByControllerAndAction
	 * @static
	 */
	static function GetInstanceByController($controller,$options = array()){
		return Atk14Form::GetInstanceByControllerAndAction($controller->controller,$controller->action,$controller,$options);
	}

	/**
	 * Gets instance of a form specified by its class name.
	 *
	 * You specify only its class name. The name is relative to current controller.
	 *
	 * In controller:
	 * <code>
	 * $form = Atk14Form::GetForm("MoveForm",$this);
	 * </code>
	 *
	 * @param string $classname
	 * @param Atk14Controller $controller instance of controller which uses the form
	 * @return Atk14Form
	 * @static
	 */
	static function GetForm($class_name,$controller = null,$options = array()){
		global $ATK14_GLOBAL;
		// preved z camelcase na underscore
		$filename = $class_name;
		$filename = preg_replace("/^([A-Z])/e","strtolower('\\1')",$filename);
		$filename = preg_replace("/([A-Z])/","_\\1",$filename);
		$filename = strtolower($filename);

		//echo $ATK14_GLOBAL->getValue("controller")."/$filename.inc"; exit;

		// zde se pokusime najit formik v adresari podle kontroleru a pokud nebude nalezen, zkusime o adresar vyse
		if(!$form = Atk14Form::GetInstanceByFilename($ATK14_GLOBAL->getValue("controller")."/$filename.inc",$controller,$options)){
			$form = Atk14Form::GetInstanceByFilename("$filename.inc",$controller,$options);
		}
		return $form;
	}

	/**
	 * Returns an instance of ApplicationForm class if exists or Atk14Form.
	 *
	 * <code>
	 * $form = Atk14Form::GetDefaultForm($controller);
	 * </code>
	 *
	 * @param Atk14Controller $controller
	 * @return Atk14Form
	 */
	static function GetDefaultForm($controller = null){
	  ($form = Atk14Form::GetForm("ApplicationForm",$controller)) || ($form = new Atk14Form(array(),$controller));
	  return $form;
	}

	/**
	 * Validates form data.
	 *
	 * @param array|Dictionary $data
	 * @return array validated and cleaned data.
	 * @uses is_valid()
	 *
	 */
	function validate($data)
	{
		if($this->is_valid($data)){
			return $this->cleaned_data;
		}
		return null;
	}

	/**
	 * Tests validity of form data.
	 *
	 * You can specify data to be checked in methods variable or you can check them in two steps. 
	 *
	 * <code>
	 * if($request->Post() && $form->is_valid($_POST)){
	 * ...
	 * }
	 * </code>
	 *
	 * This also works. First set data by set_data() method and then use is_valid() method.
	 * <code>
	 * if($request->Post() && $form->set_data($_POST) && $form->is_valid()){
	 * ...
	 * }
	 * </code>
	 *
	 * @param array|Dictionary $data data from user to be checked
	 * @return bool true if data is valid, otherwise returns false
	 * @uses Form::is_valid()
	 *
	 */
	function is_valid($data = null)
	{
		global $HTTP_REQUEST;

		isset($data) && $this->set_data($data);

		$this->_call_super_constructor();
		
		$out = parent::is_valid();

		if(
			$out &&
			$this->atk14_csrf_protection_enabled &&
			!in_array((string)$HTTP_REQUEST->getVar("_token","PG"),$this->get_valid_csrf_tokens())
		){
			$this->set_error(_("Please, submit the form again"));
			$out = false;
		}

		return $out;
	}

	/**
	 * @access private
	 */
	function _call_super_constructor()
	{
		if(!$this->atk14_super_constructor_called){
			$options = $this->atk14_constructor_options;
			$options["call_set_up"] = false;

			if(isset($this->atk14_initial_values)){ $options["initial"] = $this->atk14_initial_values; }
			if(isset($this->atk14_data)){ $options["data"] = $this->atk14_data; }

			// v konstruktoru dojde ke smazani pole $this->fields;
			// pokud mame ve fields neco, co tam bylo dodano az v controlleru,
			// timto hackem dosahneme toho, ze o predhozi hodnoty neprijdeme
			// (a navic jsou puvodni fiedls podle ocekavani zarazena az na konec)
			if(isset($this->fields)){ $orig_fields = $this->fields; }

			parent::Form($options);

			if(isset($orig_fields)){
				$this->fields = array_merge($this->fields,$orig_fields);
			}

			$this->atk14_super_constructor_called = true;
		}
	}

	/**
	 * Sets forms data.
	 *
	 * <code>
	 * $form->set_data($_POST); // parametr je pole
	 * $form->set_data($dictionary); // ale muze to byt i object tridy Dictionary
	 * </code>
	 *
	 * Method returns true so you could use it in conditions:
	 * <code>
	 *  if($this->request->Post() && $this->form->set_data($this->params) && $form->is_valid()){
	 *  	$context->setValues($form->cleaned_data);
	 *  	$this->flash->notice("Zaznam byl ulozen");
	 *  	$this->_redirect_to_action("index");
	 *  }
	 * </code>
	 *
	 * @param array|Dictionary
	 * @return true
	 *
	 */
	function set_data($data)
	{
		if(is_object($data)){ $data = $data->toArray(); }
		$this->atk14_data = $data;
		return true;
	}

	/**
	 * Sets forms action.
	 *
	 * Default action is set to current request URI.
	 *
	 * <code>
	 * $form->set_action(array(
	 *   "controller" => "customer",
	 *   "action" => "login"
	 * ));
	 * </code>
	 *
	 * @param array|string $url
	 */
	function set_action($url)
	{
		if(is_array($url)){
			$this->atk14_action = Atk14Url::BuildLink($url);
			return;
		}
		$this->atk14_action = (string)$url;
	}

	/**
	 * Sets forms method.
	 *
	 * Default forms method is set to POST. With this method you can change it to any other legal method.
	 *
	 * @param string $method  request method
	 */
	function set_method($method){
		$this->atk14_method = (string)$method;
	}

	/**
	 * Returns forms submit method.
	 *
	 * @return string
	 */
	function get_method(){
		return $this->atk14_method;
	}

	function enable_csrf_protection(){
		$this->atk14_csrf_protection_enabled = true;
		$this->set_hidden_field("_token",$this->get_csrf_token());
	}

	/**
	 * Returns initial values of fields.
	 *
	 * Get value of a specific field:
	 * <code>
	 * $email_init = $form->get_initial("email");
	 * </code>
	 *
	 * Get values of all fields:
	 * <code>
	 * $initials = $form->get_initial();
	 * </code>
	 * In this case method returns array. For example value of field email is available as $initials["email"]
	 *
	 * @param string $name name of field to check or nothing to get initial values of all fields
	 * @return mixed
	 *
	 */
	function get_initial($name = null)
	{
		if(isset($name)){
		   $out = parent::get_initial($name);
		   if(isset($this->atk14_initial_values) && in_array($name,array_keys($this->atk14_initial_values))){
			  $out = $this->atk14_initial_values[$name];
		   }
		   return $out;
		}

		$out = array();
		$keys = array_keys($this->fields);
		foreach($keys as $key){
			$out[$key] = $this->get_initial($key);
		}

		return $out;
	}

	/**
	 * Sets initial values in fields.
	 *
	 * Set up initial value of single field by using key/value pair
	 * <code>
	 * $form->set_initial("login","karel.kulek");
	 * $form->set_initial("password","heslicko");
	 * </code>
	 *
	 * You can also set up initial values of more fields by using several types of object.
	 * <ol>
	 * <li>array
	 * <code>
	 * $form->set_initial(array(
	 *    "login" => "karel.kulek",
	 *    "password" => "heslicko"
	 * ));
	 * </code>
	 * </li>
	 * <li>object of class Dictionary, usually variable $params defined in Atk14Controller
	 * <code>
	 * $this->set_initial($this->params);
	 * </code>
	 * </li>
	 * <li>object of class TableRecord
	 * <code>
	 * $this->set_initial($user);
	 * </code>
	 * </li>
	 * </ol>
	 *
	 * @param mixed $key_or_values
	 * @param string $value
	 */
	function set_initial($key_or_values,$value = null)
	{
		if(is_string($key_or_values)){ return $this->set_initial(array("$key_or_values" => $value)); }
		if(is_object($key_or_values)){ return $this->set_initial($key_or_values->toArray()); }

		if(!isset($this->atk14_initial_values)){ $this->atk14_initial_values = array(); }
		$this->atk14_initial_values = array_merge($this->atk14_initial_values,$key_or_values);
	}


	/**
	 * Sets or initializes a hidden field in a form.
	 *
	 * Setting single hidden field:
	 * <code>
	 * $form->set_hidden_field("step","1");
	 * $form->set_hidden_field("session_id","33skls");
	 * </code>
	 *
	 * Setting multiple hidden fields:
	 * <code>
	 * $form->set_hidden_field(array(
	 *       "step" => "1",
	 *       "session_id" => "33skls"
	 * ));
	 * </code>
	 *
	 * @param string|array $key_or_values name of attribute or array of key=>value pairs
	 * @param string $value value of attribute when $key_or_values set as string
	 *
	 */
	function set_hidden_field($key_or_values,$value = null)
	{
		if(is_string($key_or_values)){ return $this->set_hidden_field(array($key_or_values => $value)); }

		foreach($key_or_values as $k => $v){
			if(is_object($v)){ $key_or_values[$k] = $v->getId(); }
		}

		$this->atk14_hidden_fields = array_merge($this->atk14_hidden_fields,$key_or_values);
	}

	/**
	 * Sets form attribute(s) to $value(s).
	 *
	 * Setting single attribute:
	 * <code>
	 * $form->set_attr("enctype","multipart/form-data");
	 * </code>
	 *
	 * Setting multiple attributes:
	 * <code>
	 * $form->set_attr(array(
	 * 	"enctype" => "multipart/form-data",
	 * 	"class" => "form_common"
	 * 	));
	 * </code>
	 *
	 * @param string|array $key_or_values name of attribute or array of key=>value pairs
	 * @param string $value value of attribute when $key_or_values set as string
	 *
	 */
	function set_attr($key_or_values,$value = null)
	{
		if(is_string($key_or_values)){ return $this->set_attr(array($key_or_values => $value)); }

		$this->atk14_attrs = array_merge($this->atk14_attrs,$key_or_values);
	}

	/**
	 * Sets enctype attribute of form to value "multipart/form-data".
	 *
	 * Setting this attribute to "multipart/form-data" allows uploading data in the form.
	 */
	function allow_file_upload()
	{
		$this->set_method("post");
		$this->set_attr("enctype","multipart/form-data");
	}

	/**
	 * Alias for allow_file_upload()
	 */
	function enable_file_upload(){ return $this->allow_file_upload(); }

	/**
	 * Render start of form tag.
	 *
	 * @return string string with form starting HTML code
	 */
	function begin()
	{
		$this->_call_super_constructor();
		return "<form action=\"$this->atk14_action\" method=\"$this->atk14_method\"".$this->_get_attrs().">";
	}

	/**
	 * Render start of form tag for use with asynchronous request.
	 *
	 * @return string string with form starting HTML code
	 */
	function begin_remote()
	{
		$this->_call_super_constructor();
		// takto je to v ROR: onsubmit="new Ajax.Request('', {asynchronous:true, evalScripts:true, parameters:Form.serialize(this)}); return false;"
		//return "<form action=\"$this->atk14_action\" method=\"$this->atk14_method\" onsubmit=\"return remote_form(this);\">";
		return "<form action=\"$this->atk14_action\" method=\"$this->atk14_method\" class=\"remote_form\"".$this->_get_attrs().">";
	}

	/**
	 * @access private
	 */
	function _get_attrs(){
		$out = "";
		foreach($this->atk14_attrs as $key => $value){
			$out .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($value).'"';
		}
		return $out;
	}

	/**
	 * Renders end of form.
	 *
	 * Method first renders all defined hidden fields ($this->atk14_hidden_fields) and completes the form with corresponding ending tag </form>.
	 *
	 * @return string string with form ending HTML code
	 */
	function end()
	{
		$out = array();
		if(sizeof($this->atk14_hidden_fields)){
			$out[] = "<div>";
			reset($this->atk14_hidden_fields);
			while(list($_key,$_value) = each($this->atk14_hidden_fields))
			{
				$out[] = "<input type=\"hidden\" name=\"".htmlspecialchars($_key)."\" value=\"".htmlspecialchars($_value)."\" />";
			}
			$out[] = "</div>";
		}
		$out[] = "</form>";
		return join("\n",$out);
	}

	/**
	 * Sets own error message for current form or its field.
	 *
	 * Set error message for whole form:
	 * <code>
	 * $form->set_error("Prihlasovaci udaje nejsou spravne.");
	 * </code>
	 *
	 * <code>
	 * $form->set_error("login","Takove prihlasovaci jmeno neexistuje.");
	 * </code>
	 *
	 * @param string $error_message_or_field_name
	 * @param string $error_message error message. Required if $error_message_or_field_name specified is used as field name
	 *
	 */
	function set_error($error_message_or_field_name,$error_message = null)
	{
		if(!isset($error_message)){
			$field_name = "";
			$error_message = $error_message_or_field_name;
		}else{
			$field_name = $error_message_or_field_name;
			$error_message = $error_message;
		}

		if($field_name==""){
			$this->atk14_errors[] = $error_message;
			return;
		}

		if(!isset($this->errors)){ $this->errors = array(); }
		if(!isset($this->errors[$field_name])){ $this->errors[$field_name] = array(); }
		$this->errors[$field_name][] = $error_message;
	}

	/**
	 * Gets error messages for form fields.
	 *
	 * If not field is specified returns array with messages for all fields.
	 * When $on_field is specified method returns array for only this field.
	 *
	 * <code>
	 * $error_ar = $form->get_errors(); // pole poli chyb na vsech polich
	 * $error_ar = $form->get_errors("email"); // pole chyba na konkretnim poli
	 * </code>
	 *
	 * @param string $on_field
	 * @return array
	 */
	function get_errors($on_field = null){
		 $out = parent::get_errors();
		 if(!isset($out[""]) && sizeof($this->atk14_errors)>0){
			 $out[""] = array();
		 }
		 if(sizeof($this->atk14_errors)>0){
			 $out[""] = array_merge($out[""],$this->atk14_errors);
		 }
		 if(isset($on_field)){
			if(!isset($out[$on_field])){ $on_field[$on_field] = array(); }
			return $out[$on_field];
		 }
		 return $out;
	}

	/**
	 *
	 * @todo Comment
	 */
	function non_field_errors(){
		$errors = parent::non_field_errors();
		foreach($this->atk14_errors as $e){ $errors[] = $e; }
		return $errors;
	}

	/**
	 * Sets error title.
	 *
	 * @todo explain
	 * @param string $title
	 * @uses $atk14_error_title
	 *
	 */
	function set_error_title($title){
		$this->atk14_error_title = $title;
	}

	/**
	 * Checks wheher this form contains errors.
	 *
	 * @return bool true if there are errors otherwise false
	 */
	function has_errors()
	{
		return (sizeof($this->get_errors())>0);
	}

	/**
	 * List of errors rendered as HTML.
	 *
	 * @return string HTML code with listed errors.
	 */
	function get_error_report()
	{
		if(!$this->has_errors()){ return ""; }
		$out = array();
		$out[] = "<div class=\"errorExplanation\">";
		$out[] = "<h3>$this->atk14_error_title</h3>";
		$out[] = "<ul>";
		$errors = $this->get_errors();
		reset($errors);
		while(list($_key,$_messages) = each($errors)){
			if(sizeof($_messages)==0){ continue; }
			$_prefix = "";
			if(isset($this->fields[$_key])){
			  $_prefix = $this->fields[$_key]->label.": ";
			}
			$out[] = "<li>$_prefix".join("</li>\n<li>$_prefix",$_messages)."</li>";
		}
		$out[] = "</ul>";
		$out[] = "</div>";
		return join("\n",$out);
	}

	/**
	 * Gets instance of a {@link Field} of current form.
	 *
	 * @param string $name identifier of the field
	 * @return Field instance of Field
	 *
	 */
	// !!! je dulezite pred volanim get_field() volat konstruktor rodice.
	// !!! jinak by nebyl formular ($this) zinicialozovan (chybela by napr vlastnost $this->auto_id)
	// !!! a te je dulezita pri volani:
	// !!!   $field = $form->get_field("name");
	// !!!   echo $field->label_tag();
	function get_field($name){
		$this->_call_super_constructor();
		if(!$out = parent::get_field($name)){
			throw new Exception(get_class($this).": there is no such a field $name");
		}
		return $out;
	}

	function has_field($name){
		return isset($this->fields[$name]);
	}

	/**
	 * Turns ssl on.
	 */
	function enable_ssl(){
		global $HTTP_REQUEST;

		if(!$HTTP_REQUEST->ssl()){
			if(preg_match('/^http:/',$this->atk14_action)){
				$this->atk14_action = preg_replace('/^http:/','https:',$this->atk14_action);
			}elseif(preg_match('/^\//',$this->atk14_action)){
				$this->atk14_action = "https://".$HTTP_REQUEST->getServerName().$this->atk14_action;
			} // TODO is there an another possibility?
		}
	}

	/**
	 * Returns current CSRF token.
	 * 
	 * @return string
	 */
	function get_csrf_token(){
		$tokens = $this->get_valid_csrf_tokens();
		return $tokens[0];
	}

	/**
	 * Returns all CSRF tokens which are considered as valid.
	 * 
	 * @return string[]
	 */
	function get_valid_csrf_tokens(){
		global $HTTP_REQUEST;
		$session = &Atk14Session::GetInstance();

		$out = array();

		$t = floor(time()/(60 * 5));

		for($i=0;$i<=1;$i++){
			$_t = $t - $i;
			$out[] = sha1($_t.SECRET_TOKEN.get_class($this).$HTTP_REQUEST->getRemoteAddr().$session->getSecretToken());
		}

		return $out;
	}
}

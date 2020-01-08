<?php
/**
 * Class for advanced operations with forms.
 * @filesource
 */

/**
 * Class for advanced operations with forms.
 *
 * This class is extension of basic class Form of the form framework.
 *
 * Atk14Form adds following features:
 * - possibility to setup initial parameters of form elements later than in constructor
 * - possibility to setup sent user data later than in constructor
 * - simple addition of hidden values
 * - setup of action attribute of the form
 * - methods begin() and end() for rendering of the form to template
 * - method set_method() to change the value of forms method attribute
 *
 * ## Defining a form
 *
 * Example of a form
 *
 * forms/users/login_form.php
 * ```
 *	class LoginForm extends ApplicationForm{
 *		function set_up(){
 *			$this->add_field("login",new CharField(array(
 *			"label" => _("Login"),
 *			"help_text" => _("client identification"),
 *			"min_length" => 1,
 *			"max_length" => 64
 *			)));
 *
 *			$this->add_field("password",new CharField(array(
 *				"label" => _("Password"),
 *				"min_length" => 1,
 *				"max_length" => 64
 *			)));
 *		}
 *	}
 * ```
 * ## Advanced form validation
 *
 * Overridde {@see Form::clean()} method when it is needed to perform more complex validation such as combination of two fields.
 * For example you want to make sure that user filled email field when he checked that he wants to accept newsletter
 *```
 * class ExampleForm extends ApplicationForm {
 *
 * 	function set_up() {
 * 		$this->add_field("receive_newsletter", new BooleanField());
 * 		$this->add_field("email", new EmailField());
 * 	}
 *
 *
 * 	function clean() {
 * 		$data = $this->cleaned_data;
 * 		if ($data["receive_newsletter"]===true) {
 * 			if (!$data["email"]) {
 * 				$this->set_error("email", "Fill your email address if you want to receive out newsletter");
 * 			}
 * 		}
 *
 * 		return [null, $data];
 * 	}
 * }
 *```
 *
 * ## Using a form in a controller
 *
 * The class variable $form is available automatically if the name and path of the form matches the controllers _action_.
 * For example we have a controller UsersController containing action 'login'.
 * Then if atk14 finds class LoginForm in a file app/forms/users/login_form.php the form will be available through the class variable $form.
 *
 * ```
 *	$form = &$this->form;
 *	$form->set_initial("login","user.name");
 *	$form->set_hidden_field("action","login");
 *	if($request->post() && $form->is_valid($this->params)){
 *		// data are ok
 *		$data = $form->cleaned_data;
 *	}
 * ```
 * ### Accessing fields in a controller
 *
 * - Using {@see Atk14Form::get_field()} method
 * ```
 * echo $form->get_field("login");
 * ```
 *
 * - Access it as an array
 * ```
 * echo $form["login"];
 * ```
 *
 * ## Using a form in a template
 *
 * Basically you can print a form by using helper {form} in a template:
 * ```
 *	{form}
 *		{render partial=shared/form_field field=last_name}
 *	{/form}
 * ```
 *
 * which you can replace with this code (this actually uses {form} helper}:
 * ```
 *	{$form->begin()}
 * ```
 * prints out beginning of a <form> element
 *
 * Print out closing tag of <form> element
 * with all hidden fields inside added by $form->set_hidden_field() method inside.
 * ```
 *	{$form->end()}
 * ```
 *
 * Print out error summary.
 * ```
 *	{$form->get_error_report()}
 * ```
 *
 * @package Atk14\Core
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
	 * Initial values
	 *
	 * Must be null when values are not set
	 */
	protected $atk14_initial_values = null;

	/**
	 * Options passed to constructor
	 *
	 * @var array
	 */
	protected $atk14_constructor_options = array();

	/**
	 * Just a flag to prevent repeated call of _call_super_constructor() method
	 *
	 * @var boolean
	 */
	private $atk14_super_constructor_called = false;

	/**
	 * Common form error message
	 *
	 * @var string
	 */
	private $atk14_error_title = "";

	/**
	 * Array storing error messages set during form validation
	 *
	 * @var array
	 * @todo mark as private
	 */
	var $atk14_errors = array();

	/**
	 * request method to send the form.
	 *
	 * @var string
	 */
	protected $atk14_method = "post";

	/**
	 * Flag signalling that form is protected against csrf attack.
	 *
	 * @var bool
	 */
	var $atk14_csrf_protection_enabled = false;

	/**
	 * Constructor
	 *
	 * @param array $options valid options:
	 * - call_set_up
	 * - attrs
	 * @param Atk14Controller $controller
	 *
	 * @todo complete options
	 */
	function __construct($options = array(),$controller = null)
	{
		global $HTTP_REQUEST;

		$class_name = new String4(get_class($this));

		$options = array_merge(array(
			"call_set_up" => true, // is this really used somewhere? TODO: to be removed
			"attrs" => array(),
		),$options);

		$this->atk14_attrs = $options["attrs"];

		$this->atk14_constructor_options = $options;

		$this->atk14_action = $HTTP_REQUEST->getRequestURI();
		//$this->atk14_action = Atk14Url::BuildLink(array()); // tohle sestavi URL s akt. $lang, $namespace, $controller a $action

		$this->controller = $controller;

		$this->atk14_error_title = _("The form contains errors, therefore it cannot be processed.");

		$this->__do_small_initialization($options);

		$this->pre_set_up();
		$options["call_set_up"] && $this->set_up();
	}

	/**
	 * It is called before set_up()
	 *
	 * A perfect place for pre-initialization stuff.
	 *
	 * @ignore
	 */
	function pre_set_up(){
		
	}

	/**
	 * It is called after set_up() and even after any special field adding.
	 *
	 * Example:
	 * ```
	 *	$form = new SomeForm();
	 *	// pre_set_up() and set_up() are called somewhere within form`s constructor
	 *	$form->add_field("")
	 *
	 *	if($d = $form->validate($param)){
	 *	...
	 *	}
	 * ```
	 *
	 * @ignore
	 */
	function post_set_up(){
		
	}

	/**
	 * Creates instance of Atk14Form by filename.
	 *
	 * File with given name contains definition of form class. You have sevaral ways to specify the filename.
	 *
	 * - With directory name, then the file is expected in current namespace directory.
	 * ```
	 *	$form = Atk14Form::GetInstanceByFilename("login/login_form.inc");
	 * ```
	 * - Without directory name, then the file is expected in current controller directory.
	 * ```
	 *	$form = Atk14Form::GetInstanceByFilename("login_form");
	 * ```
	 *
	 * You don't have to specify the .inc suffix. It will be added automatically.
	 * ```
	 *	$form = Atk14Form::GetInstanceByFilename("login/login_form");
	 * ```
	 *
	 * @param string $filename name of file containing definition of a form
	 * @param Atk14Controller $controller_obj instance of controller using this form
	 * @param array $options
	 * @see __construct()
	 * @return Atk14Form
	 */
	static function GetInstanceByFilename($filename,$controller_obj = null,$options = array())
	{
		global $ATK14_GLOBAL;

		$options += array(
			"attrs" => array(),
		);

		$filename = preg_replace('/\.(inc|php)$/','',$filename); // nechceme tam koncovku

		$controller = $ATK14_GLOBAL->getValue("controller");
		$namespace = $ATK14_GLOBAL->getValue("namespace");
		$path = $ATK14_GLOBAL->getApplicationPath()."forms";

		$_action = preg_replace('/.*?([^\/]+)_form$/','\1',$filename);

		$options["attrs"] += array(
			"id" => "form_{$controller}_{$_action}",
		);

		// toto je preferovane poradi ve vyhledavani souboru s formularem
		$files = array(
		   "$path/$namespace/$filename",
		   "$path/$namespace/$controller/$filename",
		   "$path/$filename",
		   "$filename",
		);

		$filename = null;
		foreach($files as $_file){
			foreach(array("php","inc") as $suffix){
				if(file_exists("$_file.$suffix")){
					$filename = "$_file.$suffix";
					break;
				}
			}
			if(isset($filename)){ break; }
		}

		if(!isset($filename)){ return null; }

		$classname = "";
		if(preg_match('/([^\\/]+)_form\.(inc|php)$/',$filename,$matches)){
			$classname = new String4($matches[1]);
			$classname = $classname->camelize()->append("Form")->toString();
		}
		if(strlen($classname)==0 || !file_exists($filename)){
			return null;
		}
		// TODO: pokud je $filename napr. /path/to/a/project/app/forms/articles/create_new_form.php,
		// melo by dojit i k automatickemu nahrani souboru (jestlize existuje) /path/to/a/project/app/forms/articles/articles.php
		require_once($filename);

		// toto je novinka - TODO: otestovat
		preg_match('/([^\/]+)\/+[^\/]+$/',$filename,$matches);
		$_namespace = String4::ToObject($matches[1])->camelize()->toString(); // "app/forms/spam_filters/index_form.php" -> "SpamFilters"
		// pokud existuje SpamFilters\IndexForm, je tento nazev tridy pouzit
		if(class_exists($_cn = "$_namespace\\$classname",false)){
			$classname = $_cn;
		}
		// TODO: vice urovni namespaces - napr. Admin\SpamFilters\IndexForm
		// TODO: vyresit (nebo neresit? :)) walking formulare

		$form = new $classname($options,$controller_obj);
		return $form;
	}

	/**
	 * Get instance of a form by controller name and action name.
	 *
	 * ```
	 *	$form = Atk14Form::GetInstanceByControllerAndAction("login","login");
	 * ```
	 *
	 * @param string $controller name of controller
	 * @param string $action name of action
	 * @param Atk14Controller $controller_obj instance of controller using the form (optional) to set context controller
	 * @param array $options see {@link Atk14Form::__construct()}
	 * @return Atk14Form
	 */
	static function GetInstanceByControllerAndAction($controller,$action,$controller_obj = null,$options = array())
	{
		$options += array(
			"attrs" => array(),
		);
		$options["attrs"] += array(
			"id" => "form_{$controller}_{$action}",
		);

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
	 * @param array $options {@see __construct}
	 * @return Atk14Form instance of {@link Atk14Form}
	 * @uses GetInstanceByControllerAndAction
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
	 * ```
	 *	$form = Atk14Form::GetForm("MoveForm",$this);
	 * ```
	 *
	 * @param string $class_name
	 * @param Atk14Controller $controller instance of controller which uses the form
	 * @param array $options {@see __construct}
	 * @return Atk14Form
	 */
	static function GetForm($class_name,$controller = null,$options = array()){
		global $ATK14_GLOBAL;

		$s = new String4($class_name);
		$filename = $s->underscore()->lower()->toString();

		$controller_name = $ATK14_GLOBAL->getValue("controller");

		//echo $ATK14_GLOBAL->getValue("controller")."/$filename.inc"; exit;

		// zde se pokusime najit formik v adresari podle kontroleru a pokud nebude nalezen, zkusime o adresar vyse
		if(!$form = Atk14Form::GetInstanceByFilename("$controller_name/$filename",$controller,$options)){
			$form = Atk14Form::GetInstanceByFilename("$filename",$controller,$options);
		}
		return $form;
	}

	/**
	 * Returns an instance of ApplicationForm class if exists or Atk14Form.
	 *
	 * Example
	 * ```
	 *	$form = Atk14Form::GetDefaultForm($controller);
	 * ```
	 *
	 * @param Atk14Controller $controller
	 * @return Atk14Form
	 */
	static function GetDefaultForm($controller = null){
		if($controller && $controller->namespace){
			$class_name = new String4($controller->namespace);
			$class_name = $class_name->camelize()."Form";
			if($form = Atk14Form::GetForm($class_name)){
				return $form;
			}
		}
	  ($form = Atk14Form::GetForm("ApplicationForm",$controller)) || ($form = new Atk14Form(array(),$controller));
	  return $form;
	}

	/**
	 * Validates form data.
	 *
	 * Performs all validation rules specified by used fields.
	 * After this executes clean method used in the form.
	 *
	 * @param array|Dictionary $data
	 * @return array|null validated and cleaned data or error messages
	 * @uses is_valid()
	 *
	 */
	function validate($data)
	{
		if(!isset($data)){ $data = array(); }
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
	 * ```
	 *	if($request->Post() && $form->is_valid($_POST)){
	 *	...
	 *	}
	 * ```
	 *
	 * This also works. First set data by set_data() method and then use is_valid() method.
	 * ```
	 *	if($request->Post() && $form->set_data($_POST) && $form->is_valid()){
	 *	...
	 *	}
	 * ```
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

		$super_constructor_called = $this->_call_super_constructor();

		if(isset($data) && !$super_constructor_called){
			$this->__do_big_initialization(array(
				"data" => $this->atk14_data, // $this->atk14_data is set in set_data() method
			));
		}
		
		$out = parent::is_valid();

		if(
			$out &&
			$this->atk14_csrf_protection_enabled &&
			!in_array((string)$HTTP_REQUEST->getVar("_csrf_token_","PG"),$this->get_valid_csrf_tokens())
		){
			$this->set_error(_("Please, submit the form again"));
			$out = false;
		}

		return $out;
	}

	/**
	 * Checks whether any values have been changed in the form compared with its initial state
	 *
	 *
	 * ```
	 *	if($request->post() && $form->is_valid($params))){
	 *		if(!$form->changed()){
	 *			// no changes were done in the form
	 *			return;
	 *		}
	 *		$article->setValues($form->cleaned_data);
	 *	}
	 * ```
	 *
	 * Returns true or false.
	 * Returns null if the form has not yet been validated.
	 */
	function changed(){
		if(!isset($this->cleaned_data)){
			// the form has not been validated
			return null;
		}

		$flattenner = function($item) use(&$flattenner){
			if(is_array($item)){
				foreach($item as $k => $v){
					$item[$k] = $flattenner($v);
				}
			}
			if(is_object($item) && method_exists($item,"getId")){
				$item = $item->getId();
			}
			if(is_object($item) && method_exists($item,"__toString")){
				$item = (string)$item;
			}
			return $item;
		};

		$initials = $flattenner($this->get_initial());
		$d = $flattenner($this->cleaned_data);

		return $initials!=$d;
	}

	/**
	 * @access private
	 * @ignore
	 */
	function _call_super_constructor()
	{
		if(!$this->atk14_super_constructor_called){
			$options = $this->atk14_constructor_options;
			$options["call_set_up"] = false;
			$options["__do_small_initialization"] = false; // already did in constructor

			if(isset($this->atk14_initial_values)){ $options["initial"] = $this->atk14_initial_values; }
			if(isset($this->atk14_data)){ $options["data"] = $this->atk14_data; }

			parent::__construct($options);

			$this->atk14_super_constructor_called = true;
			$this->post_set_up();

			return true;
		}
		return false;
	}

	/**
	 * Sets forms data.
	 *
	 * Data can be passed as array
	 * ```
	 *	$_POST = array(
	 *		"id" => "143",
	 *	)
	 *	$form->set_data($_POST);
	 * ```
	 *
	 * or as a Dictionary object
	 * ```
	 *	$form->set_data($dictionary);
	 * ```
	 *
	 * Method returns true so you could use it in conditions:
	 * ```
	 *	if($this->request->Post() && $this->form->set_data($this->params) && $form->is_valid()){
	 *		$context->setValues($form->cleaned_data);
	 *		$this->flash->notice("Zaznam byl ulozen");
	 *		$this->_redirect_to_action("index");
	 *	}
	 * ```
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
	 * Sets forms action attribute.
	 *
	 * Default action is set to current request URI.
	 *
	 * This method recognizes several formats of the $url parameter:
	 * - array - here you can specify all parameters recognized by {@link Atk14Url::BuildLink()}
	 * ```
	 * $form->set_action(array(
	 * 	"controller" => "customer",
	 * 	"action" => "login"
	 * ));
	 * ```
	 * - only action (controller and namespace are used from current form.
	 * ```
	 *	$form->set_action("index");
	 * ```
	 * - 'controller/action' combination
	 * ```
	 *	$form->set_action("books/index");
	 * ```
	 * - URI
	 * ```
	 *	$form->set_action("/en/articles/detail/?id=123");
	 * ```
	 * - fully specified URL
	 * ```
	 *	$form->set_action("http://www.example.com/en/articles/detail/?id=123");
	 * ```
	 *
	 * @param array|string $url
	 * @see Atk14Url::BuildLink()
	 */
	function set_action($url)
	{
		$url = Atk14Url::BuildLink($url,array("connector" => "&"));
		$this->atk14_action = (string)$url;
	}

	/**
	 * Returns url to request when the form is sent.
	 *
	 * @return string url to used action
	 */
	function get_action(){
		return $this->atk14_action;
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

	/**
	 * Enables CSRF protection.
	 *
	 * The method adds new field with name '_csrf_token_' to the form
	 */
	function enable_csrf_protection(){
		$this->atk14_csrf_protection_enabled = true;
		$this->set_hidden_field("_csrf_token_",$this->get_csrf_token());
	}

	/**
	 * Returns initial values of fields.
	 *
	 * Get value of a specific field:
	 * ```
	 *	$email_init = $form->get_initial("email");
	 * ```
	 *
	 * Get values of all fields:
	 * ```
	 *	$initials = $form->get_initial();
	 * ```
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

		if(is_null($this->fields)){
			return array();
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
	 * ```
	 *	$this->form->set_initial("login","karel.kulek");
	 *	$this->form->set_initial("password","heslicko");
	 * ```
	 *
	 * You can also set up initial values of more fields by using several types of object.
	 * - array
	 * ```
	 *	$this->set_initial(array(
	 *		"login" => "karel.kulek",
	 *		"password" => "heslicko"
	 *	));
	 * ```
	 * - object of class Dictionary, usually variable $params defined in {@link Atk14Controller}
	 * ```
	 *	$this->set_initial($this->params);
	 * ```
	 * - object of class {@link TableRecord}
	 * ```
	 *	$this->set_initial($user);
	 * ```
	 *
	 * @param string|array $key_or_values
	 * @param string $value
	 */
	function set_initial($key_or_values,$value = null)
	{
		if(is_string($key_or_values)){ return $this->set_initial(array("$key_or_values" => $value)); }
		if(is_object($key_or_values)){ return $this->set_initial($key_or_values->toArray()); }

		if(!isset($this->atk14_initial_values)){ $this->atk14_initial_values = array(); }
		$this->atk14_initial_values = array_merge($this->atk14_initial_values,$key_or_values);

		foreach($key_or_values as $k => $v){
			$k = (string)$k;
			if(isset($this->fields[$k])){
				$this->fields[$k]->initial = $v;
			}
		}
	}


	/**
	 * Sets or initializes a hidden field in a form.
	 *
	 * Setting single hidden field:
	 * ```
	 *	$form->set_hidden_field("step","1");
	 *	$form->set_hidden_field("session_id","33skls");
	 * ```
	 *
	 * Setting multiple hidden fields:
	 * ```
	 *	$form->set_hidden_field(array(
	 *		"step" => "1",
	 *		"session_id" => "33skls"
	 *	));
	 * ```
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
	 * ```
	 *	$form->set_attr("enctype","multipart/form-data");
	 * ```
	 *
	 * Setting multiple attributes:
	 * ```
	 *	$form->set_attr(array(
	 *		"enctype" => "multipart/form-data",
	 *		"class" => "form_common"
	 *	));
	 * ```
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
	 * Get form attribute
	 *
	 * Example
	 * ```
	 *	echo $form->get_attr("class"); // null
	 *	$form->set_attr("class","nice");
	 *	echo $form->get_attr("class"); // "nice"
	 * ```
	 *
	 * @param string $key name of attribute
	 * @return string|null attribute value
	 */
	function get_attr($key){
		return isset($this->atk14_attrs[$key]) ? $this->atk14_attrs[$key] : null;
	}


	/**
	 * Sets enctype attribute of form to value "multipart/form-data".
	 *
	 * Setting this attribute to "multipart/form-data" allows uploading data in the form.
	 */
	function enable_multipart(){
		$this->set_method("post");
		$this->set_attr("enctype","multipart/form-data");
	}

	/**
	 * Alias for enable_multipart()
	 *
	 * @todo to be removed
	 * @obsolete
	 */
	function allow_file_upload(){ $this->enable_multipart(); }

	/**
	 * Alias for enable_multipart()
	 *
	 * @todo to be removed
	 * @obsolete
	 */
	function enable_file_upload(){ return $this->enable_multipart(); }

	/**
	 * Render start of form tag.
	 *
	 * It detects automatically when multipart encoding is needed for submission and optimizes output markup.
	 *
	 * @return string string with form starting HTML code
	 */
	function begin()
	{
		$this->_call_super_constructor();
		if($this->is_multipart()){ $this->enable_multipart(); }
		return "<form action=\"".h($this->get_action())."\" method=\"".$this->get_method()."\"".$this->_get_attrs().">";
	}

	/**
	 * Render start of form tag for use with asynchronous request.
	 *
	 * @return string string with form starting HTML code
	 */
	function begin_remote()
	{
		$this->_call_super_constructor();
		if($this->is_multipart()){ $this->enable_multipart(); }
		return "<form action=\"".h($this->get_action())."\" method=\"".$this->get_method()."\" class=\"remote_form\"".$this->_get_attrs().">";
	}

	/**
	 * @access private
	 * @todo mark the method as private
	 * @ignore
	 */
	function _get_attrs(){
		$out = "";
		foreach($this->atk14_attrs as $key => $value){
			if($key=="id" && !$value){ continue; } // we do not want something like <form action="/" method="post" id="">
			$out .= ' '.h($key).'="'.h($value).'"';
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
			foreach($this->atk14_hidden_fields as $_key => $_value)
			{
				$out[] = "<input type=\"hidden\" name=\"".h($_key)."\" value=\"".h($_value)."\" />";
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
	 * ```
	 *	$form->set_error("Prihlasovaci udaje nejsou spravne.");
	 * ```
	 *
	 * Set error message to a single field
	 * ```
	 *	$form->set_error("login","This login is already used");
	 * ```
	 *
	 * More messages can be attached to a field
	 * ```
	 *	$form->set_error("login", "login too short");
	 *	$form->set_error("login", "login can contain only alphanumeric characters");
	 * ```
	 *
	 * @param string $error_message_or_field_name
	 * @param string $error_message error message. Required if $error_message_or_field_name specified is supposed to be field name
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
	 * ```
	 *	$error_ar = $form->get_errors();
	 * ```
	 *
	 * Returns array of arrays with all error messages on all fields
	 *
	 * ```
	 *	$error_ar = $form->get_errors("email");
	 * ```
	 *
	 * Returns array of error messages on a particular field
	 *
	 * @param string $on_field name of a field to read errors from
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
	 * Returns errors that are bound to the form.
	 *
	 * Returns error messages that are bound to the form as a whole not to fields.
	 *
	 * @return array array of error messages
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
	 * Common message shown at the top of the form when there are some wrong values.
	 * Error messages for specifies form fields are set by set_error() method.
	 *
	 * @param string $title
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
		foreach($errors as $_key => $_messages){
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
	 * Gets instance of a {@link BoundField} of current form.
	 *
	 * @param string $name identifier of the field
	 * @return BoundField
	 */
	function get_field($name){
	// !!! je dulezite pred volanim get_field() volat konstruktor rodice.
	// !!! jinak by nebyl formular ($this) zinicialozovan (chybela by napr vlastnost $this->auto_id)
	// !!! a te je dulezita pri volani:
	// !!!   $field = $form->get_field("name");
	// !!!   echo $field->label_tag();
		$this->_call_super_constructor();
		if(!$out = parent::get_field($name)){
			throw new Exception(get_class($this).": there is no such field $name");
		}
		return $out;
	}

	/**
	 * Returns all fields as associative array
	 *
	 * ```
	 * $fields = $form->get_fields();
	 * ```
	 * @return BoundField[]
	 */
	function get_fields(){
		$this->_call_super_constructor();

		$fields = array();
		foreach($this->get_field_keys() as $key){
			$fields[$key] = $this->get_field($key);
		}
		return $fields;
	}

	/**
	 * Returns names of the fields as array.
	 *
	 * @return array
	 */
	function get_field_keys(){
		$this->_call_super_constructor();
		return parent::get_field_keys();
	}

	/**
	 * Returns list of fields.
	 *
	 * @param string $wildcart
	 * @return array of strings with field names
	 * @note what about the param
	 */
	function list_fields($wildcart = ""){
		$this->_call_super_constructor();
		return parent::list_fields();
	}

	/**
	 * Checks if the form contains a field.
	 *
	 * @param string $name Name of field
	 * @return boolean
	 */
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
		global $HTTP_REQUEST,$ATK14_GLOBAL;
		$session = $ATK14_GLOBAL->getSession();

		$out = array();

		if(defined("TEST") && TEST){
			// This token can be valid only in testing environment.
			// In testing this is also the current valid token.
			$out[] = "testing_csrf_token";
		}

		$t = floor(time()/(60 * 5));
		for($i=0;$i<=1;$i++){
			$_t = $t - $i;
			$out[] = sha1($_t.SECRET_TOKEN.get_class($this).$HTTP_REQUEST->getRemoteAddr().$session->getSecretToken());
		}

		return $out;
	}
}

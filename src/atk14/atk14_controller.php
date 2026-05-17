<?php
/**
 * Base class for controllers.
 *
 * @filesource
 */

/**
 * Base class for controllers.
 *
 * @package Atk14\Core
 * @author Jaromir Tomek
 *
 */
#[\AllowDynamicProperties]
class Atk14Controller{

	/**
	 * @var string
	 */
	public $page_title;

	/**
	 * @var string
	 */
	public $page_description;

	/**
	 * HTTP request object
	 *
	 * Contains information about current browsers HTTP request.
	 *
	 * @var HTTPRequest
	 *
	 */
	public $request = null;

	/**
	 * HTTP response object
	 *
	 * Contains information about response sent back to a browser.
	 *
	 * @var HTTPResponse
	 */
	public $response = null;

	/**
	 * Link to logging engine.
	 *
	 * @var Logger
	 */
	public $logger = null;

	/**
	 * Instance of Atk14Mailer class.
	 *
	 * The $mailer variable can be used to execute mailer actions in a similar way to common controller actions
	 *
	 * Note: in fact this is a Atk14MailerProxy member
	 *
	 * @todo: to be explained
	 * @var Atk14Mailer
	 */
	public $mailer = null;

	/**
	 * Name of the namespace.
	 *
	 * @var string
	 */
	public $namespace = "";

	/**
	 * Name of the controller
	 *
	 * @var string
	 */
	public $controller = "";

	/**
	 * Name of the action being currently executed
	 *
	 * @var string
	 */
	public $action = "";

	/**
	 * @var string
	 */
	public $requested_controller;

	/**
	 * @var string
	 */
	public $requested_action;

	/**
	 * Flag that controls whether layout will be rendered.
	 *
	 * Template still can be rendered independently on this variable.
	 * {@link $render_template}
	 * @var boolean
	 */
	public $render_layout = true;

	/**
	 * Name of layout template used to generate output.
	 *
	 * @var string
	 */
	public $layout_name = "";

	/**
	 * Flag that controls whether template will be rendered.
	 *
	 * Default value is true and template {@link $template_name} will be rendered.
	 *
	 * @var boolean
	 */
	public $render_template = true;

	/**
	 * Name of template to render.
	 *
	 * By default it set to $action, but you can override it by setting it anytime in a controllers method.
	 *
	 * @var string
	 */
	public $template_name = null;

	/**
	 * Current language used
	 *
	 * @var string
	 */
	public $lang = "";

	/**
	 * Flag whether this controller is in rendering component mode
	 *
	 * @var boolean
	 */
	public $rendering_component = false;

	/**
	 * In rendering component mode this is the previous (caller) namespace
	 *
	 * @var string
	 */
	public $prev_namespace = null;

	/**
	 * In rendering component mode this is the previous (caller) controller
	 *
	 * @var string
	 */
	public $prev_controller = null;

	/**
	 * In rendering component mode this is the previous (caller) action
	 *
	 * @var string
	 */
	public $prev_action = null;

	/**
	 * A flag that an action has been already executed.
	 *
	 * When an action is executed during the _before_filter() call,
	 * no another action should be executed after the _before_filter().
	 *
	 * @var signature
	 */
	public $action_executed = false;


	/**
	 * GET and POST parameters.
	 *
	 * @var Dictionary
	 */
	public $params = null;

	/**
	 * Session instance.
	 *
	 * @see Atk14Session
	 * @var Atk14Session
	 */
	public $session = null;

	/**
	 * Flash messages
	 *
	 * @var Atk14Flash
	 */
	public $flash = null;

	/**
	 * @var boolean
	 */
	public $cookies_enabled;

	/**
	 * Instance of {@link Atk14Form} used in current controllers action.
	 *
	 * @var Atk14Form
	 */
	public $form = null;

	/**
	 * Instance of {@link Atk14Sorting} class.
	 *
	 * @var Atk14Sorting
	 */
	public $sorting = null;

	/**
	 * Will be constructed before _before_render() calling.
	 * 
	 * @var Smarty
	 */
	public $smarty = null;

	/**
	 * @access private
	 */
	public $_atk14_caches_action = [];

	/**
	 * List of before_filters added by {@link _prepend_before_filter()}
	 *
	 * These are executed before controllers _before_filter() method.
	 *
	 * @var array
	 */
	private $_atk14_prepended_before_filters = [];

	/**
	 * List of before_filters added by {@link _append_before_filter()}
	 *
	 * These are executed after controllers _before_filter() method.
	 *
	 * @var array
	 */
	private $_atk14_appended_before_filters = [];

	/**
	 * List of after_filters added by {@link _prepend_after_filter()}
	 *
	 * These are executed before controllers _after_filter() method.
	 *
	 * @var array
	 */
	private $_atk14_prepended_after_filters = [];

	/**
	 * List of after_filters added by {@link _append_after_filter()}
	 *
	 * These are executed after controllers _after_filter() method.
	 *
	 * @var array
	 */
	private $_atk14_appended_after_filters = [];

	/**
	 * Data for Smarty templates
	 * @var array
	 */
	public $tpl_data = [];

	public $walking_state;

	/**
	 * Data returned by multistep form actions.
	 *
	 * Data are filled in particular form steps and sorted in an array indexed as stated in {@link _walk()} method.
	 *
	 * These data are returned at the of a step method:
	 * ```
	 *	function registration__get_user_data() {
	 *	...
	 *		return [
	 *			"login" => "terminator",
	 *			"name" => "T1000",
	 *		);
	 *	}
	 * ```
	 *
	 * ```
	 *	function registration__get_user_info() {
	 *	...
	 *		return [
	 *			"description" => "Came from future",
	 *			"special_skills" => "unbreakable",
	 *		);
	 *	}
	 * ```
	 *
	 * Then 
	 * ```
	 *	function registration__summary() {
	 *		var_dump($this->returned_by)
	 *	}
	 * ```
	 *
	 * will show something like this
	 * ```
	 *	[
	 *		"get_user_data" = ["login" => "terminator", "name" => "T1000"),
	 *		"get_user_info" => ["description" => "Came from future", "special_skills" => "unbreakable"),
	 *		)
	 * ```
	 *
	 * @var array
	 *
	 */
	public $returned_by = [];

	/**
	 * Data filled in multistep forms.
	 *
	 * In a method of a multistep form you can use
	 * ```
	 *	$this->form_data["get_data"]
	 * ```
	 * to get data inserted in a step get_data
	 *
	 * @var array
	 */
	public $form_data = [];

	/**
	 * Database engine.
	 *
	 * @var DbMole
	 */
	public $dbmole = null;

	/**
	 * Controller initialization
	 *
	 * Do not use directly, this is an internal method used by {@link Atk14Dispatcher}
	 *
	 * @param array $options
	 * - request HttpRequest
	 * - params
	 */
	function atk14__initialize($options = []){
		global $ATK14_GLOBAL;

		$options = array_merge([
			"request" => null,
			"params" => null,
		],$options);

		$this->lang = $ATK14_GLOBAL->getValue("lang");
	
		if(isset($options["request"])){
			$this->request = $options["request"];
		}else{
			$this->request = $GLOBALS["HTTP_REQUEST"];
		}
		unset($options["request"]);

		$this->response = new HTTPResponse();
		$this->params = new Dictionary($this->request->getVars("PG")); // prefering posted vars
		$this->params->merge($options["params"]);
		unset($options["params"]); // to prevent automatic setting of $this->params property later...
		$this->dbmole = &$GLOBALS["dbmole"];
		$this->logger = $ATK14_GLOBAL->getLogger();
		$this->flash = &Atk14Flash::GetInstance();
		$this->session = $GLOBALS["ATK14_GLOBAL"]->getSession();
		$this->cookies_enabled = is_object($this->session) && $this->session->cookiesEnabled();

		$this->sorting = new Atk14Sorting($this->params);

		$this->layout_name = "";
		$this->render_layout = true;	

		$this->rendering_component = false;

		foreach($options as $_key => $_value){	
			$this->$_key = $_value;
		}

		$this->mailer = Atk14MailerProxy::GetInstanceByController($this);

		$this->template_name = null; // !! $this->template_name will be set as needed in _execute_action()
		$this->render_template = true;	

		// override controller settings for XHR requests
		if($this->request->xhr()){
			$this->response->setContentType("text/javascript");
			// charset is intentionally not set here — apps may not use UTF-8
			$this->render_layout = false;
		}

		$this->tpl_data = [];

		// *** Loading base forms ***
		// If this is an ArticlesController for instance and there is a file app/forms/articles/articles_form.inc, it will be included.
		// The class ArticlesForm is ment to be the base class for forms used by the controller.
		// TODO: utilize class_autoload()
		$_base_forms = [];
		if($this->namespace!=""){ $_base_forms[] = $ATK14_GLOBAL->getApplicationPath()."/forms/$this->namespace/{$this->namespace}_form.inc"; } // ./app/forms/admin/admin_form.inc
		$_base_forms[] = $ATK14_GLOBAL->getApplicationPath()."/forms/$this->namespace/$this->controller/{$this->controller}_form.inc"; // ./app/forms/admin/articles/articles_form.inc
		foreach($_base_forms as $_base_form){
			atk14_require_once_if_exists($_base_form);
		}

		$this->_initialize();
	}

	/**
	 * @access private
	 *
	 */
	function atk14__ExecuteAction($action){ return $this->_execute_action($action,["force_to_set_template_name" => false, "force_to_initialize_form" => false]); }

	/**
	 * @access private
	 *
	 */
	function atk14__runBeforeFilters(){
		Atk14Timer::Start("running before filters");
		$filters = Atk14Utils::JoinArrays(
			$this->_atk14_prepended_before_filters,
			"before_filter",
			$this->_atk14_appended_before_filters
		);

		foreach($filters as $f){
			$f = "_$f";
			$this->$f();
			if(Atk14Utils::ResponseProduced($this)){ break; }
		}
		Atk14Timer::Stop("running before filters");
	}

	/**
	 * @access private
	 *
	 */
	function atk14__runAfterFilters(){
		Atk14Timer::Start("running after filters");
		$filters = Atk14Utils::JoinArrays(
			$this->_atk14_prepended_after_filters,
			"after_filter",
			$this->_atk14_appended_after_filters
		);

		foreach($filters as $f){
			if($f instanceof Closure){
				$f();
				continue;
			}
			$f = "_$f";
			$this->$f();
		}
		Atk14Timer::Stop("running after filters");
	}

	/**
	 * Renders error page for missing page.
	 *
	 * Renders page with error message and sets HTTP status code 404.
	 */
	function error404(){
		$this->response->notFound();
		$this->render_template = false;
	}

	/**
	 * Action rendering page for internal server error.
	 *
	 * Render page with error message and sets HTTP status code 500.
	 */
	function error500(){
		$this->response->internalServerError();
		$this->render_template = false;
	}

	/**
	 * Action rendering 503 status page.
	 *
	 * Render page with error message and sets HTTP status code 503.
	 */
	function error503(){
		$this->response->setStatusCode(503);
		$this->response->write("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
			<html><head>
			<title>503 Service unavailable</title>
			</head><body>
			<h1>503 Service unavailable</h1>
			<p>Sorry, we are doing something important at the moment! Please, come back again later.</p>
			</body></html>
		");
		$this->render_template = false;
	}

	/**
	 * Renders error page for unauthorized user.
	 *
	 * Renders page with error message and sets HTTP status 403.
	 */
	function error403(){
		$this->response->forbidden();
		$this->render_template = false;
	}

	/**
	 * Render error page for bad authorization.
	 *
	 * Renders page with error message and sets HTTP status 401. Browser prompts for username and password.
	 */
	function error401(){
		$this->response->authorizationRequired();
		$this->render_template = false;
	}

	/**
	 * Executes another action defined in current controller.
	 *
	 * The action is executed including all before/after filters and before/after render filters.
	 *
	 * The action executed inside of current action uses its template default to the executed action.
	 * Option force_to_set_template_name forces usage of template set in current action.
	 *
	 * Note that $this->form is set to instance defaulting to the calling action.
	 * If you want the controller to use the form belonging to called action set $this->form to null;
	 *
	 * ```
	 * function index() {
	 * 	$this->form = null;
	 * 	$this->_execute_action("overview");
	 * }
	 * ```
	 *
	 * function overview() {
	 * }
	 *
	 * If such a case happens, it's better use redirection
	 * ```
	 * return $this->_redirect_to_action("overview");
	 * ```
	 *
	 * @param string $action name of action executed
	 * @param array $options
	 * - <b>force_to_set_template_name</b> - use template set in current action (default: true)
	 * @access protected
	 *
	 * @uses Atk14Mailer::GetInstanceByController()
	 * @uses Atk14Form::GetInstanceByController()
	 *
	 * @todo explain options
	 */
	function _execute_action($action,$options = []){
		global $ATK14_GLOBAL;

		$options = array_merge([
			"force_to_set_template_name" => true,
			"force_to_initialize_form" => true,
		],$options);

		$this->action = $action;

		if($options["force_to_set_template_name"]){ $this->template_name = $this->action; }
		if(!isset($this->template_name)){ $this->template_name = $this->action; }
	
		$cache = null;
		$this->_atk14_read_action_cache($cache);

		// If we don't find a form designated precisely to this action, we try to find ApplicationForm or Atk14Form at last.
		// Notice: A form can be initialized in _before_filter().
		if($options["force_to_initialize_form"] || !isset($this->form)){
			($this->form = $this->_get_form()) ||
			($this->form = Atk14Form::GetInstanceByController($this)) ||
			($this->form = Atk14Form::GetDefaultForm($this));
		}

		if(!$cache){
			Atk14Timer::Start("executing action $action");
			$this->$action();
			Atk14Timer::Stop("executing action $action");
		}else{
			foreach($cache["controller_state"] as $_key => $_val){
				$this->$_key = $_val;
			}
			$this->response->setContentType($cache["response_state"]["content_type"]);
			isset($cache["response_state"]["content_charset"]) && $this->response->setContentCharset($cache["response_state"]["content_charset"]);
			$this->response->setStatusCode($cache["response_state"]["status_code"]);
			foreach($cache["response_state"]["headers"] as $_k => $_v){
				$this->response->setHeader($_k,$_v);
			}
		}

		// az po provedeni akce se v pripade XHR requestu doplni .xhr do nazvu sablony
		if(
			$this->request->xhr() && (
				file_exists($ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/$this->controller/$this->template_name.xhr.tpl") ||
				file_exists($ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/$this->template_name.xhr.tpl") || 
				file_exists($ATK14_GLOBAL->getApplicationPath()."views/$this->template_name.xhr.tpl")
			)
		){
				$this->template_name = "$this->template_name.xhr";
		}

		// if _execute_action() was already called inside the current action method,
		// skip rendering for the calling action
		if($this->action_executed){ return; }

		$this->action_executed = true;

		if(strlen((string)$this->response->getLocation())>0){
			return;
		}

		if(!$this->render_template){
			if(!$cache){
				$this->_atk14_write_action_cache($this->response->buffer);
			}else{
				$this->response->write($cache["content"]);
			}
			return;
		}

		if(is_null($this->smarty)){ $this->smarty = $this->_get_smarty(["assign_data" => false]); }
		$this->_before_render();

		// $this->render_template may be set to false in _before_render()!
		if(!$this->render_template){
			if(!$cache){
				$this->_atk14_write_action_cache($this->response->buffer);
			}else{
				$this->response->write($cache["content"]);
			}
			return;
		}

		$this->smarty->assign($this->tpl_data);

		$controller_name = $this->controller;
		$layout_template = $this->layout_name;
		$explicit_layout_name = "";


		$template_name = $this->template_name.".tpl";
		if(!$this->smarty->templateExists("$template_name")){
			Atk14Utils::ErrorLog("For controller ".($this->namespace ? "$this->namespace/" : "")."$this->controller there is no action template $template_name",$this->response);
			return $this->_after_render();
		}

		if(!$cache){
			if(!$this->rendering_component){
				$GLOBALS["__explicit_layout_name__"] = ""; // TODO: avoid using global variable
			}
			$this->smarty->assign("template_name",$this->template_name);
			$this->smarty->assign("layout_name",$this->layout_name);
			$action_content = [
				"main" => $this->smarty->fetch($template_name)
			];
			$this->smarty->assign("template_name",$this->template_name); // 2nd call prevents template_name to be overwritten inside the rendering component
			if(!$this->rendering_component){
				$explicit_layout_name = $GLOBALS["__explicit_layout_name__"];
			}
		}else{
			$action_content = $cache["content"];
			$explicit_layout_name = isset($cache["extra_values"]["explicit_layout_name"]) ? $cache["extra_values"]["explicit_layout_name"] : "";
		}

		if($this->render_layout){

			$layout_template = $explicit_layout_name ? $explicit_layout_name : $this->layout_name;
			if($layout_template==""){
				// default layout is expected in app/layouts/namespace/default.tpl
				// prior it was app/layouts/namespace/_default.tpl
				foreach([
					"$this->namespace/$controller_name.tpl",
					"$this->namespace/default.tpl",
					"$this->namespace/_default.tpl",
					"default.tpl",
					"_default.tpl",
				] as $_path){
					if(file_exists($ATK14_GLOBAL->getApplicationPath()."layouts/$_path")){
						preg_match('/([^\/.]+)\.tpl$/',$_path,$matches);
						$layout_template = $matches[1];
						break;
					}
				}
				if(!$layout_template){ $layout_template = "default"; }
			}

			if(
				strlen($layout_template)>0 &&
				!file_exists($_layout_template = $ATK14_GLOBAL->getApplicationPath()."layouts/$this->namespace/$layout_template.tpl") &&
				!file_exists($_layout_template = $ATK14_GLOBAL->getApplicationPath()."layouts/$layout_template.tpl")
			){
				Atk14Utils::ErrorLog($this->namespace ? "Missing layout template layouts/$this->namespace/$layout_template.tpl or layouts/$layout_template.tpl" : "Missing layout template layouts/$layout_template.tpl",$this->response);
				return $this->response;
			}
			
			$this->smarty->assign("layout_name",$layout_template);
			$layout_content = $this->smarty->fetch($_layout_template);
			$layout_content = str_replace("<%atk14_content[main]%>",$action_content["main"],$layout_content);
			foreach($this->smarty->getAtk14ContentKeys() as $c_key){
				// TODO: this replacement is already done in the placeholder helper...
				// does it still work? remove it?
				$layout_content = str_replace("<%atk14_content[$c_key]%>",$this->smarty->getAtk14Content($c_key),$layout_content);
			}
			$this->response->write($layout_content);

		}else{

			$this->response->write($action_content["main"]);

		}

		if(!$cache){
			$this->_atk14_write_action_cache($action_content,["explicit_layout_name" => $explicit_layout_name]);
		}

		$this->_after_render();
	}

	/**
	 * Creates a form instance if the given class exists.
	 *
	 * Name of the class can be passed in various ways.
	 * When the $class_name is not passed it is derived from current controller and action
	 *
	 * - full CamelCase string
	 * ```
	 * $form = $this->_get_form("CreateNewForm");
	 * ```
	 * - full underscored string
	 * ```
	 * $form = $this->_get_form("create_new_form");
	 * ```
	 * - short name without the _form suffix
	 * ```
	 * $form = $this->_get_form("edit");
	 * ```
	 * - relative path - the path is relative to ATK14_DOCUMENT_ROOT/app/forms. The .php extension is not needed.
	 * ```
	 * $login_form = $this->_get_form("logins/create_new_form");
	 * ```
	 *
	 * @param string $class_name
	 * @param array $options
	 * @return Atk14Form
	 */
	function _get_form($class_name = null,$options = []){
		$options = array_merge([
			"attrs" => [],
		],$options);
		if(!isset($class_name)){
			$class_name = new String4("{$this->action}_form");
		}else{
			$class_name = new String4($class_name);
		}
		$class_name = $class_name->camelize();
		if(!preg_match('/Form$/i',$class_name)){
			$class_name->append("Form");
		}

		# when a form is specified by its path, we only need its last part
		$aPath = preg_split("/\//", $class_name->toString());
		$id = new String4(array_pop($aPath));

		$id = "form_".$this->controller."_".$id->underscore();
		$id = preg_replace("/_form$/","",$id);

		$options["attrs"] = array_merge([
			"id" => $id,
		],$options["attrs"]);
		return Atk14Form::GetForm($class_name,$this,$options);
	}		

	/**
	 * Allows to render a partial template from an action method.
	 *
	 * Depending on variables passed the rendered output is either returned by the method or rendered directly to the response.
	 *
	 * When using the method to return output to a variable, it can be for example usable for producing JSONs with HTML snippets.
	 *
	 * Note that if you call _render() explicitly the method _before_render() will not be executed.
	 *
	 * ```
	 * $content = $this->_render("article_item");
	 * $content = $this->_render("article_item",[
	 * 	"from" => $articles,
	 * ));
	 *
	 * $content = $this->_render([
	 * 	"partial" => "article_item",
	 * 	"from" => $articles
	 * ));
	 * ```
	 *
	 * Also allows to render a text output to the response.
	 * ```
	 * $this->_render([
	 * 	"text" => "alert('The record has been deleted!');",
	 * ));
	 * ```
	 *
	 * Note that no template will be rendered after a text rendering.
	 *
	 * @param string|array $params_or_partial either template name or params if you want to render output directly to the response
	 * @param array $params
	 * @return string
	 */
	function _render($params_or_partial,$params = []){
		if(is_string($params_or_partial)){
			$params["partial"] = $params_or_partial;
		}else{
			$params = $params_or_partial;
		}

		// rendering a partial template
		if(isset($params["partial"])){
			if(is_null($this->smarty)){ $this->smarty = $this->_get_smarty(); }
			Atk14Require::Helper("function.render",$this->smarty);
			return smarty_function_render($params,$this->smarty);
		}

		// rendering text to the response
		if(isset($params["text"])){
			$this->render_template = false;
			$this->response->write($params["text"]);
			return;
		}
	}

	/**
	 * Returns instance of Smarty.
	 *
	 * @param array $options
	 * - assign_data - takes smarty variables from current controller's tpl_data and sets them in the new Atk14Smarty instance. [default is true]
	 * @return Atk14Smarty
	 */
	function _get_smarty($options = []){
		global $ATK14_GLOBAL;

		$options = array_merge([
			"assign_data" => true,
		],$options);

		$smarty = Atk14Utils::GetSmarty([
			$ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/$this->controller/",
			$ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/",
			$ATK14_GLOBAL->getApplicationPath()."views/",
			dirname(__FILE__)."/views/",
		],[
			"controller_name" => $this->controller,
			"namespace" => $ATK14_GLOBAL->getValue("namespace"),
		]);

		// assign values to Smarty that the action method should not override...

		// environment constants
		$smarty->assign("DEVELOPMENT",DEVELOPMENT);
		$smarty->assign("PRODUCTION",PRODUCTION);
		$smarty->assign("TEST",TEST);

		$smarty->assign("namespace",$this->namespace);
		$smarty->assign("controller",$this->controller);
		$smarty->assign("action",$this->action);
		$smarty->assign("requested_controller",$this->requested_controller);
		$smarty->assign("requested_action",$this->requested_action);
		$smarty->assign("rendering_component",$this->rendering_component);
		if($this->rendering_component){
			$smarty->assign("prev_namespace",$this->prev_namespace);
			$smarty->assign("prev_controller",$this->prev_controller);
			$smarty->assign("prev_action",$this->prev_action);
		}
		$smarty->assign("lang",$this->lang);
		$smarty->assign("public",$ATK14_GLOBAL->getPublicBaseHref());
		$smarty->assign("root",$ATK14_GLOBAL->getBaseHref());
		$smarty->assignByRef("params",$this->params);
		$smarty->assignByRef("request",$this->request);
		$smarty->assignByRef("page_title",$this->page_title); // v _before_render je mozne $this->page_title i $this->page_description zmenit a to se musi projevit ve smarty, proto assignByRef
		$smarty->assignByRef("page_description",$this->page_description);
		$smarty->assignByRef("flash",$this->flash); // !!! musi byt predavani referenci
		$smarty->assign("form",$this->form);
		if(!isset($this->tpl_data["sorting"])){
			$smarty->assignByRef("sorting",$this->sorting);
		}
		$options["assign_data"] && $smarty->assign($this->tpl_data);
		return $smarty;
	}

	function _prepend_before_filter($method_name){
		array_unshift($this->_atk14_prepended_before_filters,$method_name);
	}

	function _append_before_filter($method_name){
		array_push($this->_atk14_appended_before_filters,$method_name);
	}

	function _prepend_after_filter($method_name){
		array_unshift($this->_atk14_prepended_after_filters,$method_name);
	}

	function _append_after_filter($method_name){
		array_push($this->_atk14_appended_after_filters,$method_name);
	}
	
	/**
	* Metoda vhodna pro sestaveni retezcu before a after filtru.
	* Retezce filtru je mozne skladat i v konstruktoru,
	* ale tam na chybi kontext, ktery vznika az volanim atk14__initialize() (z dispatcheru).
	*/
	function _initialize(){

	}

	/**
	 * Tato metoda bude spustena pred samotnou action.
	 *
	 * @access protected
	 *
	 */
	function _before_filter(){
		
	}

	/**
	 * This method is executed just before template rendering, after action is executed.
	 * There is access to $this->smarty class member inside the method.
	 *
	 * @access protected
	 * 
	 */
	function _before_render(){

	}

	/**
	 * This method is executed after rendering.
	 *
	 * @access protected
	 *
	 */
	function _after_render(){
		
	}

	/**
	 * This method is executed after controllers action is executed.
	 *
	 * @access protected
	 *
	 */
	function _after_filter(){

	}

	/**
	 * Caches output of the given action(s)
	 *
	 * It's meant to be called in _before_filter()
	 *
	 * <code>
	 *		function _before_filter(){
	 * 			// caches index action only when there is no parameter in URL
	 *			if($this->action=="index" && $this->params->isEmpty()){
	 *				$this->_caches_action();
	 *			}
	 *		}
	 * </code>
	 */
	function _caches_action($options = []){
		$options = array_merge([
			"action" => $this->action,
			"salt" => "",
			"expires" => 5 * 60
		],$options);

		if(!is_array($options["action"])){
			$options["action"] = [$options["action"]];
		}
		
		foreach($options["action"] as $action){
			$this->_atk14_caches_action["$action"] = $options;
		}
	}


	/**
	 * @access private
	 *
	 */
	function _atk14_write_action_cache(&$content,$extra_values = []){
		if(!$recipe = $this->_atk14_get_action_cache_recipe()){ return; }

		if(is_object($content)){ // StringBuffer obviously
			$content_str = $content->toString();
			return $this->_atk14_write_action_cache($content_str);
		}

		$serialized = serialize([
			"content" => $content,
			"controller_state" => [
				"page_title" => $this->page_title,
				"page_description" => $this->page_description,
				"render_layout" => $this->render_layout,
				"render_template" => $this->render_template,
				"layout_name" => $this->layout_name,
				"template_name" => $this->template_name,
			],
			"response_state" => [
				"content_type" => $this->response->getContentType(),
				"content_charset" => $this->response->getContentCharset(),
				"status_code" => $this->response->getStatusCode(),
				"headers" => $this->response->getHeaders(),
			],
			"extra_values" => $extra_values,
		]);

		Files::Mkdir($recipe["dir"],$err,$err_msg);
		Files::WriteToFile($recipe["filename"],$serialized,$err,$err_msg);
	}

	/**
	 * @access private
	 *
	 */
	function _atk14_read_action_cache(&$cache){
		if(!$recipe = $this->_atk14_get_action_cache_recipe()){ return; }

		$filename = $recipe["filename"];

		if(file_exists($filename) && ((time()-filemtime($filename))<=$recipe["expires"])){
			$serialized = Files::GetFileContent($filename,$err,$err_msg);
			if(($unserialized = unserialize($serialized,["allowed_classes" => false])) && is_array($unserialized) && isset($unserialized["content"])){
				$cache = $unserialized;
				return true;
			}
		}
	}

	/**
	 * @access private
	 *
	 */
	function _atk14_get_action_cache_recipe(){
		global $ATK14_GLOBAL;

		$ar = null;
		isset($this->_atk14_caches_action[""]) && ($ar = $this->_atk14_caches_action[""]);
		isset($this->_atk14_caches_action["$this->action"]) && ($ar = $this->_atk14_caches_action["$this->action"]);
		if(!$ar){ return; }

		$namespace = $ATK14_GLOBAL->getValue("namespace");

		$dir = TEMP."/atk14_caches/actions/$namespace/$this->controller/$this->action/$this->lang";
		if($this->request->xhr()){ $dir .= "_xhr"; }
		$filename = "$dir/cache";
		if($ar["salt"]){
			if((is_string($ar["salt"]) || is_numeric($ar["salt"])) && preg_match('/^[a-zA-Z0-9_\.-]{1,60}$/',$ar["salt"])){
				$suffix = $ar["salt"];
			}else{
				// the salt just can't be fit directly into a filename
				$suffix = md5(serialize($ar["salt"]));
			}
			$filename .= "_$suffix";
		}

		return [
			"dir" => $dir,
			"filename" => $filename,
			"expires" => $ar["expires"]
		];
	}

	// ################### Building links #################################################################################################################################### //

	/**
	 * Generates a URL.
	 * 
	 * Takes parameters 'controller' and 'action' and generates a URL. When they both are missing, current values of controller and action will be used from $this->controller and $this->action.
	 *
	 * When only 'controller' is missing, $this->controller will be used
	 *
	 * When only 'action' is missing, 'index' will be used.
	 *
	 * For missing 'lang' $this->lang will be used.
	 *
	 * Pokud bude v parametrech chybet "controller" i "action", budou oba parametry
	 * nastaveny na $this->controller a $this->action.
	 *
	 * Pokud nebude v $params uveden jen "controller", bude nastav na $this->controller,
	 * chybejici $params["action"] bude nahrazen za "index" a
	 * $params["lang"] bude prip. naplnen $this->lang.
	 *
	 * ```
	 *	$url = $this->_link_to(["action" => "overview"));
	 *	$url = $this->_link_to(["action" => "detail", "id" => 2045, "format" => "xml"),["connector" => "&amp;")); // default connector is "&"
	 *	$url = $this->_link_to(); // link to the current namespace, ncontroller, action and lang
	 * ```
	 *
	 * @param array $params
	 * @param array $options options as described in {@link Atk14Url::BuildLink()}
	 *
	 * @return string
	 * @access protected
	 * @see Atk14::BuildLink()
	 *
	 */
	function _link_to($params = [],$options = []){
		$options += [
			"connector" => "&",
		];
		$__current_ary__ = [
			"action" => $this->action,
			"controller" => $this->controller,
			"namespace" => $this->namespace,
			"lang" => $this->lang
		];

		return Atk14Url::BuildLink($params,$options,$__current_ary__);
	}

	/**
	 * Generates url to an action in current controller.
	 *
	 *
	 * ```
	 *	$create_url = $this->_link_to_action("create_new");
	 *	$done_url = $this->_link_to_action("create_new",["done" => "1"));
	 *	$done_url = $this->_link_to_action("create_new",[),["connector" => "&"));
	 * ```
	 *
	 * @param string $action
	 * @param array $other_params
	 * @param array $options
	 *
	 * @see Atk14::BuildLink() for $other_params and $options description
	 */
	function _link_to_action($action,$other_params = [],$options = []){
		$other_params["action"] = $action;
		return $this->_link_to($other_params,$options);
	}

	// ################### Redirecting to somewhere ########################################################################################################################## //

	/**
	 * Realize HTTP redirection.
	 *
	 * This method is built up on HTTPResponse::setLocation() method and it offers some options to simpler pass URLs to redirect to.
	 * Unless status or moved_permanently option is specified,
	 *  - after a GET request the "302 Found" status is automatically used
	 *  - after a POST request the "303 See Other" status is automatically used
	 *
	 * Common usage of the method:
	 * ```
	 *	$this->_redirect_to("http://www.domenka.cz/pricelist.html"); // redirects to a given domain and URI
	 *	$this->_redirect_to("/en/books/"); // redirects to a given URI (param starts with '/')
	 * ```
	 * ```
	 *	$this->_redirect_to(["action" => "overview"));
	 *	$this->_redirect_to("index"); // same like $this->_redirect_to(["action" => "index"));
	 *	$this->_redirect_to("main/index"); // same like $this->_redirect_to(["controller" => "main", "action" => "index"));
	 * ```
	 *
	 * This call redirects to the same URL
	 * ```
	 *	$this->_redirect_to();
	 * ```
	 *
	 * Moving permanently
	 * ```
	 *	$this->_redirect_to(["action" => "overview"),["moved_permanently" => true));
	 * ```
	 *
	 * or
	 * ```
	 *	$this->_redirect_to(["action" => "overview"),["status" => 301));
	 * ```
	 *
	 *
	 * @param array|string $params
	 * @param array $options
	 * @access protected
	 *
	 * @uses HTTPResponse::setLocation()
	 * @return string
	 */
	function _redirect_to($params = [],$options = []){
		$options = array_merge([
			"connector" => "&",
			"status" => null, // 301, 302...
		],$options);
		$url = $this->_link_to($params,$options);

		// after a POST request the status 303 is automatically used
		// http://en.wikipedia.org/wiki/HTTP_303
		if(!isset($options["moved_permanently"]) && !isset($options["status"]) && $this->request->post()){
			$options["status"] = 303;
		}
		$this->response->setLocation($url,$options);

		return $url;
	}

	/**
	 * Redirect to an action in current controller.
	 *
	 * Examples:
	 * ```
	 * $this->_redirect_to_action("overview");
	 * $this->_redirect_to_action("overview",["offset" => 10));
	 * $this->_redirect_to_action("overview",["offset" => 10),["moved_permanently" => true));
	 * ```
	 * @param string $action
	 * @param array $other_params parameters to build url query part
	 * @param array $options control redirection attributes (status code ...)
	 * - status - force set http status code to this value. otherwise it is set automatically (see {@link _redirect_to()})
	 * - moved_permanently - causes redirect to generate 301 status code
	 * - connector - character joining query parameters
	 */

	function _redirect_to_action($action,$other_params = [],$options = []){
		$other_params["action"] = $action;
		return $this->_redirect_to($other_params,$options);
	}


	/**
	 * Provides redirection to SSL
	 *
	 * Typical usage in _before_filter() (or yet better in _application_before_filter())
	 *
	 * ```
	 * if(!$this->request->ssl()){
	 * 	$this->_redirect_to_ssl();
	 * 	return;
	 * }
	 * ```
	 * @param array $options
	 * - **moved_permanently** sets 301 http status code
	 * @return string
	 */
	function _redirect_to_ssl($options = []){
		$options += [
			"moved_permanently" => true,
		];
		$url = "https://".$this->request->getHTTPHost().$this->request->getRequestURI();
		$this->_redirect_to($url,$options);
		return $url;
	}

	/**
	 * Provides redirection to the unencrypted communication
	 *
	 * Usage in _before_filter() (or yet better in _application_before_filter())
	 *
	 * ```
	 *	if($this->request->ssl()){
	 *		$this->_redirect_to_no_ssl();
	 *		return;
	 *	} 
	 * ```
	 * @return string
	 */
	function _redirect_to_no_ssl(){
		$this->_redirect_to($url = "http://".$this->request->getHTTPHost().$this->request->getRequestURI());
		return $url;
	}

	// ################### Finding an object for action ###################################################################################################################### //

	/**
	 * Attempt to instantiate object by object_name and parameter
	 *
	 * <code>
	 *	 $this->_find("user");
	 *	 $this->_find("page","page_id");
	 *	 $this->_find("page",[
	 *			"key" => "page_id",
	 *			"execute_error404_if_not_found" => false,
	 *	 ));
	 * </code>
	 *
	 * When an object is instantiated it can by found as
	 *	$this->logged_user
	 *	$this->tpl_data["logged_user"]
	 *
	 * A very common usage is:
	 * <code>
	 *	function _before_filter(){
	 *		if(in_[$this->action,["detail","edit","destroy"))){
	 *			$this->_find("article");
	 *		}
	 *	}
	 * </code>
	 */
	function _find($object_name,$options = []){
		if(is_string($options)){
			$options = ["key" => $options];
		}

		$options += [
			"key" => "id",
			"id" => null, // 123
			"execute_error404_if_not_found" => true,
			"class_name" => null, // e.g. "User"

			"set_object_as_controller_property" => true,
			"add_object_to_template" => true,
		];

		if(!$options["class_name"]){
			$options["class_name"] = String4::ToObject($object_name)->camelize()->toString(); // page -> Page
		}

		$key = $options["key"];

		$id = isset($options["id"]) ? $options["id"] : $this->params->getInt($key);

		$object = Cache::Get($options["class_name"],$id);

		$options["set_object_as_controller_property"] && ($this->$object_name = $object);
		$options["add_object_to_template"] && ($this->tpl_data["$object_name"] = $object);

		if(!$object){
			$options["execute_error404_if_not_found"] && $this->_execute_action("error404");
		}

		return $object;
	}

	/**
	 * Just finds an object with no magic on the background
	 *
	 * ... or returns null when the object was not found.
	 *
	 *	$article = $this->_just_find("article");
	 *	$article = $this->_just_find("article","article_id");
	 *	$article = $this->_just_find("article",123);
	 *	$article = $this->_just_find("article",["id" => 123));
	 */
	function _just_find($object_name,$options = []){
		if(is_numeric($options)){
			$options = ["id" => $options];
		}elseif(is_string($options)){
			$options = ["key" => $options];
		}
		$options["execute_error404_if_not_found"] = false;
		$options["set_object_as_controller_property"] = false;
		$options["add_object_to_template"] = false;
		return $this->_find($object_name,$options);
	}


	// ################### Saving return URI & Redirecting back ############################################################################################################## //

	/**
	 * Adds return_uri to the given form to it's hidden parameters.
	 *
	 * $this->_save_return_uri();
	 * $this->_save_return_uri($this->form);
	 *
	 *	controller SomeController extends ApplicationController{
	 *		function edit(){
	 *			// may be also in _before_filter()
	 *			$this->_save_return_uri();
	 *			if($this->params->defined("storno")){ return $this->_redirect_back(); }
	 *
	 *			if($this->request->post() && ($d = $this->form->validate($this->params))){
	 *				// ...
	 *			}
	 *		}
	 *	}
	 */
	function _save_return_uri(&$form = null){

		// An experiment: let's utilize the session for better "redirect back" ability
		if($this->request->get()){
			($return_uris = $this->session->g("return_uris")) || ($return_uris = []);
			$key = md5($this->request->getRequestUri());
			if(!isset($return_uris[$key])){
				if(count($return_uris)>50){ array_shift($return_uris); } // for safety reasons there is a max limit
				$return_uris[$key] = $this->_get_return_uri(null);
				$this->session->s("return_uris",$return_uris);
			}
		}

		if(!isset($form)){ $form = $this->form; }
		$return_uri = $this->_get_return_uri(null);
		$form->set_hidden_field("_return_uri_",$return_uri);
	}

	/**
	 * Returns true when the given URI is safe to redirect to.
	 * Accepts only relative paths (starting with /) to prevent open redirect attacks.
	 * Rejects absolute URLs (http://...) and protocol-relative URLs (//...).
	 *
	 *	$this->_is_safe_return_uri("/"); // true
	 *	$this->_is_safe_return_uri("/admin/"); // true
	 *	$this->_is_safe_return_uri("//evil.com"); // false
	 */
	function _is_safe_return_uri($uri){
		$uri = (string)$uri;
		if(!strlen($uri)){ return false; }
		return $uri[0] === '/' && (strlen($uri) === 1 || $uri[1] !== '/');
	}

	/**
	 * Returns current return uri
	 *
	 * In fact this returns a previously saved uri (by calling $this->_save_return_uri()), value of parameter _return_uri_ (eventually return_uri) or the http referer
	 */
	function _get_return_uri($default = "index",$options = []){
		$options += [
			"consider_referer" => true,
		];

		$key = md5($this->request->getRequestUri());
		($return_uris = $this->session->g("return_uris")) || ($return_uris = []);

		foreach([
			$this->params->getString("_return_uri_"),
			$this->params->getString("return_uri"),
			isset($return_uris[$key]) ? $return_uris[$key] : null,
		] as $candidate){
			if($this->_is_safe_return_uri($candidate)){ return $candidate; }
		}

		if($options["consider_referer"] && ($referer = $this->request->getHttpReferer())){
			$server_url = $this->request->getServerUrl();
			if($server_url && strpos($referer,$server_url)===0){
				$referer = substr($referer,strlen($server_url));
			}
			if($this->_is_safe_return_uri($referer)){ return $referer; }
		}

		return $default ? $this->_link_to($default) : null;
	}

	/**
	 * Redirects user back to return_uri, when it is know.
	 * Otherwise redirects to the $default.
	 *
	 * $this->_redirect_back(); // same as "index" :)
	 * $this->_redirect_back("index");
	 * $this->_redirect_back("books/index");
	 * $this->_redirect_back([...));
	 * $this->_redirect_back($this->_link_to([...)));
	 * $this->_redirect_back("http://www.atk14.net");
	 */
	function _redirect_back($default = "index"){
		$key = md5($this->request->getRequestUri());
		($return_uris = $this->session->g("return_uris")) || ($return_uris = []);

		$return_uri = "";

		if(isset($return_uris[$key])){
			$return_uri = $return_uris[$key]; // can be an empty string
			unset($return_uris[$key]);
			$this->session->s("return_uris",$return_uris);
		}

		if(!$return_uri){
			$return_uri = $this->_get_return_uri($default);
		}

		return $this->_redirect_to($return_uri);
	}

	// ################### Methods for walking ############################################################################################################################### //

	/**
	 * This method is used for creating actions with multiple steps.
	 *
	 *
	 * ```
	 *	$this->_walk([
	 *		"get_domain_name",
	 *		"get_data",
	 *		"register",
	 *		"done"
	 *	));
	 * ```
	 *
	 * V $options["extra_params"] mohou byt uvedeny dalsi parametry, ktere sa budou automaticky prevadet pri kazdem presemerovani a
	 * vlozi se automaticky do action atributu formularu.
	 *
	 * ```
	 *	$this->_walk([
	 *		"get_password",
	 *		"rules_agreement",
	 *		"confirm",
	 *		"done",
	 *		),
	 *		["extra_params" => [ "hashed_id" => $this->password_sender->getHashedId()))
	 *	);
	 * ```
	 *
	 * @param array $steps Array with identifiers for each step.
	 * @param array $options Extra options
	 * @access protected
	 */
	function _walk($steps,$options = []){
		$options = array_merge([
			"extra_params" => [],
		],$options);

		$steps = array_values($steps); // Conversion to indexed array

		$this->steps = $steps;
		$this->step_id = ""; // e.g. "8c6725e6c523e8321698cbb939549cc1-5"
		$this->walking_secret = ""; // e.g "8c6725e6c523e8321698cbb939549cc1"
		$this->current_step_index = null; // 0, 1, 2...
		$this->current_step_name = ""; // e.g. "get_password"
		$this->form_data = [];
		$this->returned_by = [];

		$this->_walking_extra_params = $options["extra_params"]; // for setting the action attribute in forms

		$logging = 0;
		$logger = &$this->logger;

		if(preg_match("/^([a-zA-Z0-9]{32})-([0-9]{1,3})$/",(string)$this->request->getVar("step_id"),$matches)){
			$step_unique = $matches[1];
			$request_index = (int)$matches[2];
		}else{
			$step_unique = (string)String4::RandomString(32);
			$request_index = 0;
		}

		// important to mix up current namespace/controller/action into the session name
		$session_name = "step_{$step_unique}_".substr(md5($this->namespace.$this->controller.$this->action),16);
		if(!$state = $this->session->getValue($session_name)){
			$state = [
				"current_step_index" => 0,
				"form_data" => [],
				"returned_by" => [],
				// "step_unique" 
			];
		}
		$this->walking_state = &$state;
		$state["step_unique"] = $step_unique;
		$state["step_session_name"] = $session_name;

		$session_index = $state["current_step_index"];

		$logging && $logger->debug("request: $request_index, session: $session_index");
	
		// index ze session i z requestu musi byt stejny...
		if($request_index>$session_index){
			// uzivatel se vratil zpet v prohlizeci,
			// ale ted se pohybuje zase dopredu ->
			// presmerujeme ho zpatky tam, kde ma byt :)
			$logging && $logger->debug("redirecting to: $session_index");
			return $this->_redirect_to(array_merge($this->_walking_extra_params,["step_id" => "$step_unique-$session_index","step" => $steps[$session_index]]));
		}
		if($session_index>$request_index){
			// user is navigating back in browser ->
			// discard steps taken after the current position
			for($i=$request_index;$i<=$session_index;$i++){
				$logging && $logger->debug("unsetting: $i");
				$_step = $steps[$i];
				unset($state["form_data"][$_step]);
				unset($state["returned_by"][$_step]);
			}
			$session_index = $request_index;
			$state["current_step_index"] = $request_index;
			
			// __save_walking_state() is intentionally not called here.
			// State is saved when user submits a form, allowing free back/forward browser navigation.
		}

		$this->_execute_current_step();
	}

	/**
	 * Transparently passes over to next step.
	 *
	 * Current step is executed and then the action proceeds to the next step without user being noticed.
	 * The procedure is done without http redirection.
	 * Status is saved to session.
	 *
	 * ```
	 *	function action__step(){
	 *		if(!$this->do_we_need_to_execute_this_step){
	 *			return $this->_next_step();
	 *		}
	 *	}
	 * ```
	 * Use of return is important!!
	 *
	 * Custom parameters can be passed, for example values returned from current step.
	 *
	 * @return null
	 */
	function _next_step($current_step_returns = true){
		$state = $this->_save_walking_state($current_step_returns);

		$this->_execute_current_step();

		// This method must return null due to typical usage.
		return null;
	}

	/**
	 * Saves state of current step.
	 *
	 * @access private
	 */
	function _save_walking_state($current_step_returns){
		$state = &$this->walking_state;
		$step_index = $state["current_step_index"];
		$step = $this->steps[$step_index];

		$state["returned_by"][$step] = $current_step_returns;
		if(isset($this->form) && isset($this->form->cleaned_data)){
			$state["form_data"][$step] = $this->form->cleaned_data;
		}
		$state["current_step_index"]++;
		$this->__save_walking_state($state);
		return $state;
	}

	function __save_walking_state($state){
		$this->session->setValue($state["step_session_name"],$state);
	}

	function _execute_current_step(){
		if($ret = $this->__execute_current_step()){
			$state = $this->_save_walking_state($ret);
			$steps = $this->steps;

			return $this->_redirect_to(array_merge($this->_walking_extra_params,["step_id" => "$state[step_unique]-$state[current_step_index]","step" => $steps[$state["current_step_index"]]]));
		}
	}

	function __execute_current_step(){
		$state = &$this->walking_state;
		$step_unique = $state["step_unique"];
		$step_index = $state["current_step_index"];
		$step = $this->steps[$step_index];

		$this->step_id = "$step_unique-$step_index";

		// if a form is found for this step, use it;
		// jinak pouzijeme defaultni formik
		($this->form = Atk14Form::GetInstanceByFilename("$this->controller/{$this->action}/{$step}_form",$this)) || ($this->form = Atk14Form::GetDefaultForm($this));

		if(isset($this->form)){
			$this->form->set_hidden_field("step_id",$this->step_id);
			$this->form->set_action($this->_link_to($this->_walking_extra_params,["connector" => "&"]));
		}

		$method_name = "{$this->action}__$step";
		$this->template_name = "{$this->action}/$step";

		$this->form_data = $state["form_data"];
		$this->returned_by = $state["returned_by"];
		$this->tpl_data["form_data"] = $state["form_data"];
		$this->tpl_data["step_id"] = $this->step_id;
		$this->tpl_data["current_step_index"] = $this->current_step_index = $state["current_step_index"];
		$this->tpl_data["current_step_name"] = $this->current_step_name = $this->steps[$this->current_step_index];
		$this->walking_secret = $state["step_unique"];
		if($out = $this->_before_walking()){
			return $out;
		}
		if($this->action_executed){ // e.g. $this->_execute_action("error404")
			return;
		}
		return $this->$method_name();
	}
	
	/**
	 * Method _before_walking() will be executed just before step method.
	 *
	 * Member variables $this->form_data and $this->returned_by are set.
	 *
	 * If a non-false value is returned, the certain step method will not be executed
	 * and user will be redirected to the next step.
	 *
	 * The returned value will be accessible through $this->returned_by["%step_name%"].
	 */
	function _before_walking(){

	}

	function _clear_walking_state(){
		$state = &$this->walking_state;
		if(!$state){ return; }
		$this->session->clear($state["step_session_name"]);
	}
}

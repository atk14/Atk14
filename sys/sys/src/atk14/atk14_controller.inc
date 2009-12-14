<?

class Atk14Controller{

	var $action_executed = false;
	var $_atk14_caches_action = array();
	var $_atk14_prepended_before_filters = array();
	var $_atk14_appended_before_filters = array();
	var $_atk14_prepended_after_filters = array();
	var $_atk14_appended_after_filters = array();

	function atk14__initialize($options){
		global $ATK14_GLOBAL;
		$this->lang = $ATK14_GLOBAL->getValue("lang");
		$this->langz = $this->lang;
		if($this->langz=="cs"){ $this->langz = "cz"; } 
	
		if(isset($options["request"])){
			$this->request = $options["request"];
		}else{
			$this->request = &$GLOBALS["HTTP_REQUEST"];
		}
		unset($options["request"]);

		$this->response = new HTTPResponse();
		$this->params = new Dictionary($this->request->getVars("PG")); // prefering posted vars
		$this->params->merge($options["params"]);
		unset($options["params"]); // aby pozdeji nedoslo k automatickemu nastaveni vlastnosti $this->params...
		$this->dbmole = &$GLOBALS["dbmole"];
		$this->logger = &$GLOBALS["ATK14_LOGGER"];
		$this->flash = &Atk14Flash::GetInstance();

		$this->session = &Atk14Session::GetInstance();
		$this->cookies_enabled = $this->session->cookiesEnabled();

		$this->layout_name = "";
		$this->render_layout = true;	

		$this->rendering_component = false;

		reset($options);
		while(list($_key,$_value) = each($options)){
			$this->$_key = $_value;
		}

		$this->template_name = null; // !! $this->template_name bude podle potreb nastaveno az v _execute_action()
		$this->render_template = true;	

		//prenastaveni controlleru v pripade, ze toto je XHR request
		if($this->request->xhr()){
			$this->response->setContentType("text/javascript");
			//pozor!! nasteveni charsetu zakomentovavam... neni mozne uvazovat, ze vsechny aplikace pojdenou v UTF-8
			//$this->response->setContentCharset("UTF-8");
			$this->render_layout = false;
		}

		$this->tpl_data = array();

		$this->_initialize();
	}

	function atk14__ExecuteAction($action){ return $this->_execute_action($action,array("force_to_set_template_name" => false)); }

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

	function atk14__runAfterFilters(){
		Atk14Timer::Start("running after filters");
		$filters = Atk14Utils::JoinArrays(
			$this->_atk14_prepended_after_filters,
			"after_filter",
			$this->_atk14_appended_after_filters
		);

		foreach($filters as $f){
			$f = "_$f";
			$this->$f();
		}
		Atk14Timer::Stop("running after filters");
	}

	function index(){
		Atk14Utils::ErrorLog("Atk14Controller::index(): Hey Dude, cover me!",$this->response);
		$this->render_template = false;
	}

	function error404(){
		if($this->request->xhr()){
			$this->response->write("alert('Requested URI was not found!');");
		}else{
			$this->response->notFound();
		}
		$this->render_template = false;
	}

	function error500(){
		$this->response->internalServerError();
		$this->render_template = false;
	}

	function error403(){
		$this->response->forbidden();
		$this->render_template = false;
	}

	function _execute_action($action,$options = array()){
		global $ATK14_GLOBAL;

		$options = array_merge(array(
			"force_to_set_template_name" => true,
		),$options);

		$this->action = $action;

		if($options["force_to_set_template_name"]){ $this->template_name = $this->action; }
		if(!isset($this->template_name)){ $this->template_name = $this->action; }
	
		$cache = null;
		$this->_atk14_read_action_cache($cache);

		// pokud nenajdeme formular urceny presne pro tuto akci, pokusime se najit ApplicationForm a nakonec Atk14Form
		if(!isset($this->form)){ // pokud uz mame form z _before_filter, tak ho prece nezahodime - TODO: jsem zarazeny z toho, ze se _before_filter() pouzsti drive
			($this->form = Atk14Form::GetInstanceByControler($this)) || ($this->form = Atk14Form::GetDefaultForm($this));
		}

		$this->mailer = Atk14Mailer::GetInstanceByControler($this);

		if(!$cache){
			Atk14Timer::Start("executing action $action");
			$this->$action();
			Atk14Timer::Stop("executing action $action");
		}else{
			foreach($cache["controller_state"] as $_key => $_val){
				$this->$_key = $_val;
			}
		}

		// az po provedeni akce se v pripade XHR requestu doplni .xhr do nazvu sablony
		if(
			$this->request->xhr() && (
				file_exists($ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/$this->controller/$this->template_name.xhr.tpl") ||
				file_exists($ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/$this->template_name.xhr.tpl")
			)
		){
				$this->template_name = "$this->template_name.xhr";
		}

		// pokud v aktualni action metode doslo k volani
		// $this->_execute_action(), nemuzeme pro volajici action generovat zadny vystup!
		if($this->action_executed){ return; }

		$this->action_executed = true;

		if($this->render_template && strlen($this->response->getLocation())==0){
			$this->_before_render();

			$controller_name = $this->controller;

			$layout_template = $this->layout_name;
			if($this->render_layout && $layout_template==""){
				if(file_exists($ATK14_GLOBAL->getApplicationPath()."layouts/$this->namespace/$controller_name.tpl")){
					$layout_template = $controller_name;
				}else{
					$layout_template = "_default";
				}
			}
			if(strlen($layout_template)>0 && !file_exists($ATK14_GLOBAL->getApplicationPath()."layouts/$this->namespace/$layout_template.tpl")){
				Atk14Utils::ErrorLog("Hey Dude,  missing layout template: layouts/$this->namespace/$layout_template.tpl",$this->response);
				return $this->response;
			}

			$template_name = $this->template_name.".tpl";
			if(
				!file_exists($ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/$controller_name/$template_name") &&
				!file_exists($ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/$template_name")
			){
				Atk14Utils::ErrorLog("Hey Dude, missing action template: views/$this->namespace/$controller_name/$template_name or views/$this->namespace/$template_name!",$this->response);
				return $this->_after_render();
			}

			$smarty = Atk14Utils::GetSmarty(array(
				$ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/$controller_name/",
				$ATK14_GLOBAL->getApplicationPath()."views/$this->namespace/",
				$ATK14_GLOBAL->getApplicationPath()."views/",
				dirname(__FILE__)."/views/",
			),array(
				"controller_name" => $controller_name
			));
			// nabindovani hodnot do smarty, ktere by volani action metody nemelo zmenit...
			$smarty->assign("namespace",$this->namespace);
			$smarty->assign("controller",$this->controller);
			$smarty->assign("action",$this->action);
			$smarty->assign("requested_controller",$this->requested_controller);
			$smarty->assign("requested_action",$this->requested_action);
			$smarty->assign("lang",$this->lang);
			$smarty->assign("langz",$this->langz);
			$smarty->assign("public",$ATK14_GLOBAL->getPublicBaseHref());
			$smarty->assign("root",$ATK14_GLOBAL->getBaseHref());
			$smarty->assign_by_ref("params",$this->params);
			$smarty->assign_by_ref("request",$this->request);
			$smarty->assign("page_title",$this->page_title);
			$smarty->assign("page_description",$this->page_description);
			$smarty->assign_by_ref("flash",$this->flash); // !!! musi byt predavani referenci
			$smarty->assign_by_ref("form",$this->form);
			// do compile_id zahrneme jmeno controlleru, aby nedochazelo ke kolizim se sablonama z ruznych controlleru, ktere se jmenuji stejne
			$smarty->compile_id = $smarty->compile_id."_ATK14_".$this->namespace."_{$this->controller}_";
			reset($this->tpl_data);
			while(list($_key,) =each($this->tpl_data)){
				$smarty->assign($_key,$this->tpl_data[$_key]);
			}
			$smarty->atk14_contents = array();

			if(!$cache){
				$action_content = array(
					"main" => $smarty->fetch($template_name)
				);
			}else{
				$action_content = $cache["content"];
			}
			$action_content = array_merge($smarty->atk14_contents,$action_content);

			if($this->render_layout){
				$smarty->atk14_contents = $action_content;
				$layout_content = $smarty->fetch($ATK14_GLOBAL->getApplicationPath()."layouts/$this->namespace/$layout_template.tpl");
				foreach($smarty->atk14_contents as $c_key => $c){
					$layout_content = str_replace("<%atk14_content[$c_key]%>",$c,$layout_content);
				}
				$this->response->write($layout_content);
			}else{
				$this->response->write($action_content["main"]);
			}

			if(!$cache){
				$this->_atk14_write_action_cache($action_content);
			}

			return $this->_after_render();
		}
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
	*/
	function _before_filter(){
		
	}

	function _before_render(){

	}

	function _after_render(){
		
	}

	/**
	* Tato metoda bude spustena po spusteni action.
	*
	*/
	function _after_filter(){

	}

	function _caches_action($options = array()){
		$options = array_merge(array(
			"action" => $this->action,
			"salt" => "",
			"expires" => 5 * 60
		),$options);

		if(!is_array($options["action"])){
			$options["action"] = array($options["action"]);
		}
		
		foreach($options["action"] as $action){
			$this->_atk14_caches_action["$action"] = $options;
		}
	}

	function _atk14_write_action_cache(&$content){
		if(!$recipe = $this->_atk14_get_action_cache_recipe()){ return; }

		$serialized = serialize(array("content" => $content,"controller_state" => array(
			"page_title" => $this->page_title,
			"page_description" => $this->page_description,
			"render_layout" => $this->render_layout,
			"layout_name" => $this->layout_name,
			"template_name" => $this->template_name,
		)));

		files::mkdir($recipe["dir"],$err,$err_msg);
		files::write_to_file($recipe["filename"],$serialized,$err,$err_msg);
	}

	function _atk14_read_action_cache(&$cache){
		if(!$recipe = $this->_atk14_get_action_cache_recipe()){ return; }

		$filename = $recipe["filename"];

		if(file_exists($filename) && ((time()-filemtime($filename))<=$recipe["expires"])){
			$serialized = files::get_file_content($filename,$err,$err_msg);
			if(($unserialized = unserialize($serialized)) && is_array($unserialized) && isset($unserialized["content"])){
				$cache = $unserialized;
				return true;
			}
		}
	}

	function _atk14_get_action_cache_recipe(){
		global $ATK14_GLOBAL;

		$ar = null;
		isset($this->_atk14_caches_action[""]) && ($ar = $this->_atk14_caches_action[""]);
		isset($this->_atk14_caches_action["$this->action"]) && ($ar = $this->_atk14_caches_action["$this->action"]);
		if(!$ar || (defined("DEVELOPMENT") && DEVELOPMENT)){ return; }	

		$namespace = $ATK14_GLOBAL->getValue("namespace");

		$dir = TEMP."/atk14_caches/actions/$namespace/$this->controller/$this->action/$this->lang";
		if($this->request->xhr()){ $dir .= "_xhr"; }
		$filename = "$dir/cache";
		if($ar["salt"]){ $filename .= "_$ar[salt]"; }

		return array(
			"dir" => $dir,
			"filename" => $filename,
			"expires" => $ar["expires"]
		);
	}



	/**
	* Sestavi link.
	*
	* Pokud bude v parametrech chybet "controller" i "action", budou oba parametry
	* nastaveny na $this->controller a $this->action.
	* 
	* Pokud nebude v $params uveden jen "controller", bude nastav na $this->controller,
	* chybejici $params["action"] bude nahrazen za "index" a
	* $params["lang"] bude prip. naplnen $this->lang.
	*
	* $url = $this->_link_to(array("action" => "overview"));
	* $url = $this->_link_to(array("action" => "overview", "id" => 2045),array("connector" => "&")); // defaultni connector je "&amp;"
	* $url = $this->_link_to(); // link na akt controller, action a jazyk
	*
	* @return string
	*/
	function _link_to($params = array(),$options = array()){
		settype($params,"array");

		if(!isset($params["action"]) && !isset($params["controller"])){ $params["action"] = $this->action; }
		if(!isset($params["controller"])){ $params["controller"] = $this->controller; }
		if(!isset($params["action"])){ $params["action"] = "index"; }
		if(!isset($params["lang"])){ $params["lang"] = $this->lang; }

		return Atk14Url::BuildLink($params,$options);
	}

	/**
	* Realize HTTP redirection.
	*
	*		$this->_redirect_to("http://www.domenka.cz/");
	*		$this->_redirect_to(array("action" => "overview"));
	*		$this->_redirect_to(); // redirect to the same URL
	*
	* 
	* Moving permanently
	* 	$this->_redirect_to(array("action" => "overview",array("moved_permanently" => true)));
	* or
	* 	$this->_redirect_to(array("action" => "overview",array("status" => 301)));
	* 
	* 
	* Unless status or moved_permanently option is specified,
	*  - after a GET request the "302 Found" status is automatically used
	*  - after a POST request the "303 See Other" status is automatically used
	*/
	function _redirect_to($params = array(),$options = array()){
		if(is_string($params)){
			$url = $params;
		}else{
			$url = $this->_link_to($params,array("connector" => "&"));
		}

		// after a POST request the status 303 is automatically used
		// http://en.wikipedia.org/wiki/HTTP_303
		if(!isset($options["moved_permanently"]) && !isset($options["status"]) && $this->request->post()){
			$options["status"] = 303;
		}
		$this->response->setLocation($url,$options);
	}

	/**
	* $this->_redirect_to_action("overview");
	* $this->_redirect_to_action("overview",array("offset" => 10));
	* $this->_redirect_to_action("overview",array("offset" => 10),array("moved_permanently" => true));
	*/
	function _redirect_to_action($action,$other_params = array(),$options = array()){
		$other_params["action"] = $action;
		return $this->_redirect_to($other_params,$options);
	}


	function _redirect_to_ssl(){
		if($this->request->get() && !$this->request->sslActive()){
			$this->_redirect_to("https://".$this->request->getHTTPHost().$this->request->getRequestURI());
			return true;
		}
		return false;
	}

	function _redirect_to_no_ssl(){
		if($this->request->get() && $this->request->sslActive()){
			$this->_redirect_to("http://".$this->request->getHTTPHost().$this->request->getRequestURI());
			return true;
		}
		return false;	
	}

	/**
	* $this->_walk(array(
	*		"get_domain_name",
	*		"get_data",
	*		"register",
	*		"done"
	*	));
	*
	* V $options["extra_params"] mohou byt uvedeny dalsi parametry, ktere sa budou automaticky prevadet pri kazdem presemerovani a
	* vlozi se automaticky do action atributu formularu.
	*
	*		$this->_walk(array(
	*			"get_password",
	*			"rules_agreement",
	*			"confirm",
	*			"done",
	*		),
	*			array("extra_params" => array( "hashed_id" => $this->password_sender->getHashedId()))
	*		);
	*/
	function _walk($steps,$options = array()){
		$options = array_merge(array(
			"extra_params" => array(),
		),$options);

		$this->steps = $steps;
		$this->step_id = "";
		$this->form_data = array();
		$this->returned_by = array();

		$this->_walking_extra_params = $options["extra_params"]; // pro nastaveni action atributu ve formularich

		$logging = 0;
		$logger = &$this->logger;

		if(preg_match("/^([a-f0-9]{32})-([0-9]{1,3})$/",$this->request->getVar("step_id"),$matches)){
			$step_unique = $matches[1];
			$request_index = (int)$matches[2];
		}else{
			$step_unique = md5(uniqid(rand()));
			$request_index = 0;
		}

		$session_name = "step_$step_unique";
		if(!$state = $this->session->getValue($session_name)){
			$state = array(
				"current_step_index" => 0,
				"form_data" => array(),
				"returned_by" => array(),
				// "step_unique" 
			);
		}
		$this->walking_state = &$state;
		$state["step_unique"] = $step_unique;

		$session_index = $state["current_step_index"];

		$logging && $logger->debug("request: $request_index, session: $session_index");
	
		// index ze session i z requestu musi byt stejny...
		if($request_index>$session_index){
			// uzivatel se vratil zpet v prohlizeci,
			// ale ted se pohybuje zase dopredu ->
			// presmerujeme ho zpatky tam, kde ma byt :)
			$logging && $logger->debug("redirecting to: $session_index");
			return $this->_redirect_to(array_merge($options["extra_params"],array("step_id" => "$step_unique-$session_index","step" => $steps[$session_index])));
		}
		if($session_index>$request_index){
			// uzivatel se vraci zpet v prohlizeci ->
			// kroky, ktere udelal drive zapomeneme
			for($i=$request_index;$i<=$session_index;$i++){
				$logging && $logger->debug("unsetting: $i");
				$_step = $steps[$i];
				unset($state["form_data"][$_step]);
				unset($state["returned_by"][$_step]);
			}
			$session_index = $request_index;
			$state["current_step_index"] = $request_index;
		}

		if($ret = $this->_execute_current_step()){
			$this->_save_walking_state($ret);
			return $this->_redirect_to(array_merge($options["extra_params"],array("step_id" => "$step_unique-$state[current_step_index]","step" => $steps[$state["current_step_index"]])));	
		}
	}

	/**
	* Transparentne prejde (aniz by byl uzivatel presmerovan na dalsi krok).
	* Ulozi stav do session.
	* !!! Ve step metodach je nutne volat timto zpusobem:
	* 	return $this->_next_step();
	*	!!! Pouziti return je zde dulezite.
	*
	* Jako parametr je mozne dat to, co vraci aktualni krok.
	*
	* @return vystup z dalsiho kroku
	*/
	function _next_step($current_step_returns = true){
		$this->_save_walking_state($current_step_returns);
		return $this->_execute_current_step();
	}

	function _save_walking_state($current_step_returns){
		$state = &$this->walking_state;
		$step_index = $state["current_step_index"];
		$step = $this->steps[$step_index];

		$state["returned_by"][$step] = $current_step_returns;
		if(isset($this->form) && isset($this->form->cleaned_data)){
			$state["form_data"][$step] = $this->form->cleaned_data;
		}
		$state["current_step_index"]++;
		$this->session->setValue("step_".$state["step_unique"],$state);
	}

	function _execute_current_step(){
		$state = &$this->walking_state;
		$step_unique = $state["step_unique"];
		$step_index = $state["current_step_index"];
		$step = $this->steps[$step_index];

		$this->step_id = "$step_unique-$step_index";

		// pokud najdeme formular pro tento step, pouzijeme ho;
		// jinak pouzijeme defaultni formik
		($this->form = Atk14Form::GetInstanceByFilename("$this->controller/{$this->action}/{$step}_form.inc",$this)) || ($this->form = Atk14Form::GetDefaultForm($this));

		if(isset($this->form)){
			$this->form->set_hidden_field("step_id",$this->step_id);
			$this->form->set_action($this->_link_to($this->_walking_extra_params));
		}

		$method_name = "{$this->action}__$step";
		$this->template_name = "{$this->action}/$step";

		$this->form_data = $state["form_data"];
		$this->returned_by = $state["returned_by"];
		$this->tpl_data["form_data"] = $state["form_data"];
		$this->tpl_data["step_id"] = $this->step_id;
		if($out = $this->_before_walking()){
			return $out;
		}
		return $this->$method_name();
	}
	
	/**
	* Method _before_walking() will be executed just before step method.
	* Member variables $this->form_data and $this->returned_by are set.
	* 
	* If a non-false value is returned, the certain step method will not be executed
	* and user will be redirected to the next step.
	* The returned value will be accessible through $this->returned_by["%step_name%"].
	*/
	function _before_walking(){
		
	}

}

function atk14_smarty_prefilter($tpl_source, &$smarty){
	$tpl_source = strtr($tpl_source,array(
		"\\{" => "{literal}{{/literal}",
		"\\}" => "{literal}}{/literal}",
	));
	/*
	if(preg_match("/^([^\s]+)\*\s*Smarty\s*\*([^\s]+)/",$tpl_source,$matches)){
		 $smarty->left_delimiter = $matches[1];
		 $smarty->right_delimiter = $matches[2];
		 $tpl_source = preg_replace("/^([^\s]+)\*\s*Smarty\s*\*([^\s]+)/","",$tpl_source);
	}
	*/
	return $tpl_source;
}
?>

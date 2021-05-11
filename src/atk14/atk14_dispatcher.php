<?php
/**
 * Application dispatcher. Main static method which executes all steps of the application.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Application dispatcher. Main static method which executes all steps of the application.
 *
 * This si mostly the only line in the main applications' file `dispatcher.php` besides reading required files.
 *
 * ```
 * Atk14DispatcherDispatch::Dispatch();
 * ```
 * Processing a request consists of these steps:
 * - initialization -
 * - before filters
 * - action (form initialization, mailer initialization)
 * - before render
 * - after render
 * - after filters
 * - flush output
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @todo Write some explanation
 */
class Atk14Dispatcher{
	function Atk14Dispatcher($options = array()){
		
	}

	/**
	 * @static
	 * @param array $options
	 * @return HTTPReponse
	 */
	static function Dispatch($options = array()){
		global $HTTP_RESPONSE,$HTTP_REQUEST, $ATK14_GLOBAL, $_GET;

		$options = array_merge(array(
			"display_response" => true,
			"request" => null,
			"return_controller" => false
		),$options);

		$request = isset($options["request"]) ? $options["request"] : $HTTP_REQUEST;

		// defaultni content-type a charset
		$HTTP_RESPONSE->setContentType("text/html");
		$HTTP_RESPONSE->setContentCharset(DEFAULT_CHARSET);

		$logger = $ATK14_GLOBAL->getLogger();

		if(defined("MAINTENANCE") && MAINTENANCE){

			$HTTP_RESPONSE->setStatusCode(503);
			if(file_exists(ATK14_DOCUMENT_ROOT."/config/error_pages/error503.phtml")){
				ob_start();
				include(ATK14_DOCUMENT_ROOT."/config/error_pages/error503.phtml");
				$content = ob_get_contents();
				ob_end_clean();
			}else{
				$content = "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
					<html><head>
					<title>503: Service Unavailable</title>
					</head><body>
					<h1>"._("Service Unavailable")."</h1>
					<p>"._("This site is in maintenance. Please come back later.")."</p>
					</body></html>
				";
			}
			$HTTP_RESPONSE->write($content);
			$HTTP_RESPONSE->flushAll();
			die;

			// We can't do this here! Routers are not loaded at the time of calling this.
			// Some errors may occur in before filters.
			//$ctrl = Atk14Dispatcher::ExecuteAction("application","error503",array("namespace" => "", "request" => $request, "return_controller" => true));

		}else{

			Atk14Timer::Start("Atk14Url::RecognizeRoute");
			$route_ar = Atk14Url::RecognizeRoute($uri = $request->getRequestUri(),array(
				"get_params" => $request->getGetVars(),
			));
			$route_ar["get_params"] = is_object($route_ar["get_params"]) ? $route_ar["get_params"]->toArray() : $route_ar["get_params"];

			if(DEVELOPMENT){
				// logging
				if($route_ar["action"]=="error404"){
					$logger->warn("no route for ".$request->getRequestUri());
				}else{
					$logger->info($request->getRequestMethod()." ".$request->getUrl()); // GET http://myapp.localhost/en/main/about/
				}
				$logger->flush();
			}

			$_GET = array_merge($_GET,$route_ar["get_params"]);
			Atk14Timer::Stop("Atk14Url::RecognizeRoute");

			if(strlen($uri)==strlen($route_ar["force_redirect"])){
				// Here solving PHP's dot to underscore conversion.
				// If the uri contains a parametr with dot in it's name, PHP silently converts it to underscore.
				// Thus such URL:
				// 		http://www.myapp.com/en/books/detail/?id=1&in.format=xml
				// should not be redirected to
				// 		http://www.myapp.com/en/books/detail/?id=1&in_format=xml
				$_meaningful_redirect = false;
				for($i=0;$i<strlen($uri);$i++){
					if($uri[$i]==$route_ar["force_redirect"][$i]){ continue; }
					if($uri[$i]=="." && $route_ar["force_redirect"][$i]=="_"){ continue; }
					$_meaningful_redirect = true;
					break;
				}
				if(!$_meaningful_redirect){ $route_ar["force_redirect"] = null; }
			}

			if($request->get() && strlen($route_ar["force_redirect"])>0 && !$request->xhr()){
				$HTTP_RESPONSE->setLocation($route_ar["force_redirect"],array("moved_permanently" => true));
				$options["display_response"] && $HTTP_RESPONSE->flushAll();

				$ctrl = null;
				if($options["return_controller"]){
					$ctrl = Atk14Dispatcher::ExecuteAction($route_ar["controller"],$route_ar["action"],array(
						"page_title" => $route_ar["page_title"],
						"page_description" => $route_ar["page_description"],
						"return_controller" => true,
						"request" => $request
					));
					$ctrl->response->setLocation($route_ar["force_redirect"],array("moved_permanently" => true));
				}

				return Atk14Dispatcher::_ReturnResponseOrController($HTTP_RESPONSE,$ctrl,$options);
			}

			// prestehovano Atk14Url::RecognizeRoute()
			//i18n::init_translation($route_ar["lang"]); // inicializace gettextu

			$ATK14_GLOBAL->setValue("namespace",$route_ar["namespace"]);
			$ATK14_GLOBAL->setValue("lang",$route_ar["lang"]);

			$ctrl = Atk14Dispatcher::ExecuteAction($route_ar["controller"],$route_ar["action"],array(
				"page_title" => $route_ar["page_title"],
				"page_description" => $route_ar["page_description"],
				"return_controller" => true,
				"request" => $request
			));

			// ajaxove presmerovani...
			if(strlen($ctrl->response->getLocation())>0 && $request->xhr() && !preg_match('/^(text|application)\/(html|json|xml)/',$request->getHeader("Accept"))){
				// tohle by snad melo byt vraceno pokud je v requestu
				//	Accept: */*
				//	Accept: text/javascript
				//	neco dalsiho?
				//
				// regularni vyraz ma vyradit toto:
				//	Accept: text/html, ...
				//	Accept: text/json, ...
				//	Accept: application/json, ...
				//	Accept: text/xml, ...
				$ctrl->response->write("location.replace('".$ctrl->response->getLocation()."');"); // watch out, it's javascript
				$ctrl->response->setLocation(null);
			}

		}

		$HTTP_RESPONSE->concatenate($ctrl->response);
		$options["display_response"] && $HTTP_RESPONSE->flushAll();

		$logger->stop();

		return Atk14Dispatcher::_ReturnResponseOrController($HTTP_RESPONSE,$ctrl,$options);
	}

	/**
	 * Executes action in a controller.
	 *
	 * @static
	 * @param string $controller_name
	 * @param string $action
	 * @param array $options
	 * @return HTTPReponse
	 */
	static function ExecuteAction($controller_name,$action,$options = array()){
		global $ATK14_GLOBAL;
		static $prev_rc_controller; // previous rendering component controller

		$logger = $ATK14_GLOBAL->getLogger();

		$options = array_merge(array(
			"page_title" => "",
			"page_description" => "",
			"render_layout" => true,
			"apply_render_component_hacks" => false,
			"params" => array(),
			"return_controller" => false,
			"request" => null,
			"namespace" => (string)$ATK14_GLOBAL->getValue("namespace"), // may be null
		),$options);

		$namespace = $options["namespace"];

		$requested_controller = $controller_name;
		$requested_action = $action;

		if(!$options["apply_render_component_hacks"]){
			$prev_rc_controller = null;
		}

		if($options["apply_render_component_hacks"]){
			$prev_namespace = $ATK14_GLOBAL->getValue("namespace");
			$prev_controller_name = $ATK14_GLOBAL->getValue("controller");
			$prev_action = $ATK14_GLOBAL->getValue("action");
		}

		$ATK14_GLOBAL->setValue("namespace",$namespace);
		$ATK14_GLOBAL->setValue("controller",$controller_name);
		$ATK14_GLOBAL->setValue("action",$action);

		Atk14Utils::LoadControllers("{$controller_name}_controller");

		$_base_controller_class_name = "ApplicationController";
		$_controller_name = "application";
		if($namespace!=""){
			$_s = String4::ToObject("{$namespace}_controller")->camelize()->toString();
			if(class_exists($_s)){
				$_base_controller_class_name = $_s;
				$_controller_name = $namespace;
			}
		}

		$_class_name = String4::ToObject("{$controller_name}_controller")->camelize()->toString();
		if(!class_exists($_class_name) || preg_match("/__/",$controller_name)){
			DEVELOPMENT && $logger->error("controller class $_class_name doesn't exist");
			$controller_name = $_controller_name;
			$_class_name = $_base_controller_class_name;
			$action = "error404";
		}
		$controller = new $_class_name();

		$methods = get_class_methods($controller);
		// pokud se v nazvu akce objevi dve podtrzitka, nespustime ji, toto se nesmi 
		if(preg_match("/__/",$action) || !in_array($action,$methods)){
			DEVELOPMENT && $logger->error("there's no action method $_class_name::$action()");
			// tady se meni controller na instance tridy ApplicationController
			$controller = new $_base_controller_class_name();
			$controller_name = $_controller_name;
			$action = "error404";
		}

		$initialize_params = array(
			"namespace" => $namespace,
			"controller" => $controller_name,
			"action" => $action,
			"requested_controller" => $requested_controller,
			"requested_action" => $requested_action,
			"page_title" => $options["page_title"],
			"page_description" => $options["page_description"],
			"render_layout" => $options["render_layout"],
			"params" => $options["params"],
			"rendering_component" => $options["apply_render_component_hacks"],
			"request" => $options["request"]
		);
		if($options["apply_render_component_hacks"]){
			$initialize_params["prev_namespace"] = $prev_namespace;
			$initialize_params["prev_controller"] = $prev_controller_name;
			$initialize_params["prev_action"] = $prev_action;
		}

		$controller->atk14__initialize($initialize_params);

		$controller->atk14__runBeforeFilters();

		if($options["apply_render_component_hacks"]){
			$controller->response->setLocation(null);
			$controller->response->setStatusCode(200);
			$controller->response->clearOutputBuffer();
		}

		// pokud vstupni filter nastavi presmerovani apod, nepokracujeme dale...
		// ve vstupnim filteru je dokonce mozne volat $this->_execute_action, cimz se nastavi $this->action_executed na true...
		if(!Atk14Utils::ResponseProduced($controller)){
			$controller->atk14__ExecuteAction($action);
		}

		// sem bylo presunuto mazani flash zprav,
		// protoze v _after_filter muze byt zavolan $this->dbmole->Commit()...
		if(!$options["apply_render_component_hacks"]){
			$flash = &Atk14Flash::GetInstance();
			$flash->clearMessagesIfRead();
		}

		$controller->atk14__runAfterFilters();

		if($options["apply_render_component_hacks"]){
			$prev_rc_controller = $controller;
			$ATK14_GLOBAL->setValue("namespace",$prev_namespace);
			$ATK14_GLOBAL->setValue("controller",$prev_controller_name);
			$ATK14_GLOBAL->setValue("action",$prev_action);
		}

		if(!$options["apply_render_component_hacks"] && $prev_rc_controller && $controller->response->getStatusCode()==200 && $prev_rc_controller->response->getLocation()){
			// Beware that a redirection from the previous rendering_component controller is passed to the current controller
			// (but as you can see higher, a redirection from a before filter is silently discarded)
			$controller->response->setLocation($prev_rc_controller->response->getLocation());
			$controller->response->setStatusCode($prev_rc_controller->response->getStatusCode());
			$controller->response->buffer->clear();
		}

		return Atk14Dispatcher::_ReturnResponseOrController($controller->response,$controller,$options);
	}

	/**
	 * @ignore
	 */
	static private function _ReturnResponseOrController($response,$controller,$options){
		if($options["return_controller"]){ return $controller; }
		return $response;
	}
}

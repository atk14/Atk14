<?
class Atk14Client{
	function Atk14Client(){
		$this->session = &Atk14Session::GetInstance();
		$this->flash = &Atk14Flash::GetInstance();

		if(!isset($GLOBALS["_COOKIE"])){ $GLOBALS["_COOKIE"] = array(); }
		$GLOBALS["_COOKIE"]["_chk_"] = "1";
		$GLOBALS["_SERVER"]["REMOTE_ADDR"] = "0.0.0.0";
	}

	function disableCookies(){
		$GLOBALS["_COOKIE"] = array();
	}

	function post($path,$params = array()){
		$request = new HTTPRequest();

		$this->flash->reset();

		$path_ar = split("/",$path);

		$namespace = null;
		if(sizeof($path_ar)==2){
			$lang = "en";
			list($controller,$action) = $path_ar;
		}
		if(sizeof($path_ar)==3){
			list($lang,$controller,$action) = $path_ar;
		}
		if(sizeof($path_ar)==4){
			list($namespace,$lang,$controller,$action) = $path_ar;
		}

		$request->setMethod("POST");
		$request->setPostVars($params);
		$request->setUri(Atk14Url::BuildLink(array(
			"namespace" => $namespace,
			"action" => $action,
			"controller" => $controller,
			"lang" => $lang
		),array("connector" => "&")));

		$ctrl = Atk14Dispatcher::Dispatch(array(
			"display_response" => false,
			"request" => $request,
			"return_controller" => true
		));

		$this->controller = $ctrl;

		return $ctrl;
	}

	function getContent(){
		return $this->controller->response->buffer->toString();
	}

	function getStatusCode(){
		return $this->controller->response->getStatusCode();
	}
}
?>

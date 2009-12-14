<?
class Atk14Mailer{
	var $from = "atk14@mailer.cz";
	var $to = "";
	var $subject = "";
	var $body = "";
	var $cc = "";
	var $bcc = "";
	var $content_type = "text/plain";
	var $content_charset = "UTF-8";
	var $_attachments = array();

	var $template_name = "";
	var $tpl_data = array();

	var $namespace = null;

	function GetInstanceByControler($controller){
		$out = null;
		if(Atk14Require::Load("controllers/$controller->namespace/application_mailer.inc")){
			$out = new ApplicationMailer();
			$out->namespace = $controller->namespace;
		}

		return $out;
	}

	/**
	*
	* $mailer->execute("registration_notification",array(
	*		"user" => $user
	*	));
	*
	*/
	function execute($action,$params = array()){
		global $ATK14_GLOBAL;

		$this->body = ""; // reset body, opetovne volani by NEvyvolalo vygenerovani sablony

		$this->template_name = $action;

		$this->_before_filter();
		$this->$action($params);

		if(strlen($this->body)==0){
			$namespace = $this->namespace;

			$smarty = Atk14Utils::GetSmarty(array(
				$ATK14_GLOBAL->getApplicationPath()."views/$namespace/mailer/",
				$ATK14_GLOBAL->getApplicationPath()."views/$namespace/",
			));

			reset($this->tpl_data);
			while(list($_key,) =each($this->tpl_data)){
				$smarty->assign($_key,$this->tpl_data[$_key]);
			}

			$smarty->compile_id = $smarty->compile_id."_ATK14MAILER_{$namespace}_";

			$template_name = $this->template_name.".tpl";

			$this->body = $smarty->fetch($template_name);
		}

		return $this->_send();
	}

	function add_attachment($content,$filename = "data",$mime_type = "application/octed-stream"){
		$this->_attachments[] = array(
			"filename" => $filename,
			"mime_type" => $mime_type,
			"body" => $content
		);
	}

	function clear_attachments(){ $this->_attachments = array(); }

	// zatim jen _before_filter,
	// myslim, ze nic dalsiho nebude treba
	function _before_filter(){ }

	function _send(){
		return sendmail(array(
			"from" => $this->from,
			"to" => $this->to,
			"cc" => $this->cc,
			"bcc" => $this->bcc,
			"subject" => $this->subject,
			"body" => $this->body,
			"mime_type" => $this->content_type,
			"charset" => $this->content_charset,
			"attachments" => $this->_attachments
		));
	}
}
?>

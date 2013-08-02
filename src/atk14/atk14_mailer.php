<?php
/**
 * Class for sending emails.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Class for sending emails.
 *
 * Atk14Mailer works similar to Atk14Controller. ApplicationMailer class which is descendent of Atk14Mailer contains actions (methods).
 * These actions can be called from a controller by {@link execute()} method with the name of action as parameter.
 *
 * The action prepares data for a template which is associated to it. Data for the template are passed (like in a controller) in $tpl_data.
 * The action also sets all important parameters of the email (sender, recipients, subject ...)
 * After the action is executed the email is sent.
 * You can define the {@link _before_filter()} method which is called before every action.
 *
 * Template is a standard Smarty template. Name of the template is composed of the actions name and .tpl suffix and is stored in directory mailer.
 *
 * This is how an action is called in a controller:
 * <code>
 * $this->mailer->execute("registration_notification",array(
 *		"user" => $user
 *	));
 * </code>
 *
 * The called action is defined in application_mailer.inc:
 * <code>
 * class ApplicationMailer extends Atk14Mailer {
 * 	function registration_notification($params) {
 * 		$this->from = "info@atk14.net";
 * 		$this->to = "atk@developers.net";
 * 		$this->subject = "News from Atk14 developers";
 * 		...
 * 		$this->tpl_data["user"] = $params["user"];
 * 	}
 * }
 * </code>
 *
 * Example of template:
 * mailer/registration_notification.tpl
 * <code>
 * 	Hello {$user->getFullName()|h},
 *
 *  this is Atk14 developers newsletter.
 *
 *  ...
 *  ...
 *
 * </code>
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 */
class Atk14Mailer{

	/**
	 * senders email address
	 *
	 * e.g. john.doe@example.com
	 *
	 * @var string
	 */
	var $from = DEFAULT_EMAIL;

	/**
	 * senders name
	 *
	 * e.g. John Doe
	 */
	var $from_name = "";

	/**
	 * if $return_path is not set, $from is used automatically 
	 *
	 * @var string
	 */
	var $return_path = null;

	/**
	 * recipients email address
	 *
	 * @var string
	 */
	var $to = "";

	/**
	 * Subject
	 * 
	 * @var string
	 */
	var $subject = "";

	/**
	 * Message
	 *
	 * @var string
	 */
	var $body = "";

	/**
	 * This is a HTML version of body for a multipart/alternative email
	 *
	 * Please note that sending multipart/related emails is an experimental feature
	 */
	var $body_html = "";


	/**
	 * Email address to send copy to.
	 *
	 * @var string
	 */
	var $cc = "";

	/**
	 * Blind carbon copy.
	 *
	 * @var string
	 */
	var $bcc = "";

	/**
	 * Content type. Defaults to 'text/plain'.
	 *
	 * @var string
	 */
	var $content_type = "text/plain";

	/**
	 * Content charset.
	 *
	 * @var string
	 */
	var $content_charset = DEFAULT_CHARSET;

	/**
	 * Attachments
	 *
	 * @access private
	 * @var array
	 */
	var $_attachments = array();

	/**
	 * Template name
	 *
	 * @var string
	 * @access private
	 */
	var $template_name = "";

	/**
	 * Data passed to a template.
	 *
	 * @var array
	 */
	var $tpl_data = array();

	/**
	 * Application namespace. Is copied from controller when new instance of Atk14Mailer is created.
	 *
	 * @var string
	 * @access private
	 */
	var $namespace = null;

	/**
	 *
	 * @var Logger
	 */
	var $logger = null;

	/**
	 * Creates instance of Atk14Mailer depending on a controller.
	 *
	 * The returned class ApplicationMailer must be defined by application in controllers/_namespace_/application_mailer.(inc|php)
	 *
	 * Returns null when the ApplicationMailer class is not defined.
	 *
	 * @param Atk14Controller $controller
	 * @return ApplicationMailer
	 * @static
	 */
	static function GetInstanceByController($controller){
		return Atk14Mailer::GetInstance(array(
			"namespace" => $controller->namespace,
			"logger" => $controller->logger
		));
	}

	static function GetInstance($options = array()){
		$options = array_merge(array(
			"namespace" => "",
			"logger" => null
		),$options);

		$namespace = $options["namespace"];
		$logger = isset($options["logger"]) ? $options["logger"] : $GLOBALS["ATK14_LOGGER"];

		$mailer = null;
		if(Atk14Require::Load("controllers/$namespace/application_mailer.*")){
			$mailer = new ApplicationMailer();
			$mailer->namespace = $namespace;
			$mailer->logger = $logger;
		}
		if(!$mailer && $namespace){
			// In current namespace there is no $mailer?
			// Gonna load mailer from default namespace...
			$options["namespace"] = "";
			return Atk14Mailer::GetInstance($options);
		}
		return $mailer;
	}

	/**
	 * Method to send the email.
	 *
	 * Method executes action $action which is actually name of method in ApplicationMailer class. Additional optional parameters are passed as array.
	 *
	 * Method is called in a controller.
	 * <code>
	 * $this->mailer->execute("registration_notification",array(
	 *		"user" => $user
	 *	));
	 * </code>
	 *
	 * @param string $action name of action to be executed
	 * @param array $params optional additional parameters
	 */
	function execute($action,$params = array()){
		global $ATK14_GLOBAL;

		$this->body = $this->body_html = ""; // reset body, opetovne volani by NEvyvolalo vygenerovani sablony

		$this->template_name = $action;

		$this->_before_filter();
		$this->$action($params);

		if(strlen($this->body)==0){
			$namespace = $this->namespace;

			$smarty = Atk14Utils::GetSmarty(array(
				$ATK14_GLOBAL->getApplicationPath()."views/$namespace/mailer/",
				$ATK14_GLOBAL->getApplicationPath()."views/$namespace/",
				$ATK14_GLOBAL->getApplicationPath()."views/",
			),array(
				"namespace" => $namespace,
				"compile_id_salt" => "mailer",
			));

			$this->_before_render();

			foreach($this->tpl_data as $k => $v){	
				$smarty->assign($k,$v);
			}

			$template_name = $this->template_name.".tpl";
			$html_template_name = $this->template_name.".html.tpl";

			$this->body = $smarty->fetch($template_name);
			if($smarty->templateExists($html_template_name)){
				$this->body_html = $smarty->fetch($html_template_name);
			}
			$this->_after_render();
		}

		return $this->_send();
	}

	/**
	 * Method adds attachment to the message.
	 * @todo comment better
	 *
	 * @param mixed $content
	 * @param string $filename
	 * @param string $mime_type
	 *
	 */
	function add_attachment($content,$filename = "data",$mime_type = "application/octet-stream"){
		$this->_attachments[] = array(
			"filename" => $filename,
			"mime_type" => $mime_type,
			"body" => $content
		);
	}

	/**
	 * Removes all attachments
	 *
	 * Should be usefull when several messages with different attachments are sent through a single instance.
	 */
	function clear_attachments(){ $this->_attachments = array(); }


	/**
	 * This method is called before every action in ApplicationMailer
	 *
	 * @access protected
	 */
	function _before_filter(){ }

	/**
	 * This method is called just before rendering body
	 *
	 * @access protected
	 */
	function _before_render(){ }

	/**
	 * This method is called just after rendering body
	 *
	 * @access protected
	 */
	function _after_render(){ }

	/**
	 * Calls sendmail function and pass it all important fields to construct the message and send it.
	 *
	 * @access private
	 * @return array
	 * @uses sendmail()
	 *
	 */
	function _send(){
		$params = array(
			"from" => $this->from,
			"from_name" => $this->from_name,
			"return_path" => $this->return_path,
			"to" => $this->to,
			"cc" => $this->cc,
			"bcc" => $this->bcc,
			"subject" => $this->subject,
			"body" => $this->body,
			"mime_type" => $this->content_type,
			"charset" => $this->content_charset,
			"attachments" => $this->_attachments
		);
		if($this->body_html){
			// !! experimental feature
			$params["plain"] = $params["body"]; // oups! there is no param named body
			$params["html"] = $this->body_html;
			unset($params["body"]);
			unset($params["mime_type"]); // mime_type is determined automatically, "multipart/alternative" by default
			$email_ar = sendhtmlmail($params);
		}else{
			$email_ar = sendmail($params);
		}
		if(DEVELOPMENT){
			// logging e-mail data as we are developing
			$this->logger->info("Sending an e-mail (not for real in DEVELOPMENT)\n---------------------------------------------\nTo: $email_ar[to]\nSubject: $email_ar[subject]\n$email_ar[headers]\n\n$email_ar[body]");
		}
		return $email_ar;
	}
}

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
 * $this->mailer->execute("notify_user_registration",$user,$password);
 * </code>
 *
 * The called action is defined in application_mailer.inc:
 * <code>
 * class ApplicationMailer extends Atk14Mailer {
 * 	function notify_user_registration($user,$plain_text_password) {
 * 		$this->from = "info@atk14.net";
 * 		$this->to = $user->getEmail();
 * 		$this->subject = "Welcome to SiliconeWisdom.com";
 * 		...
 * 		$this->tpl_data["user"] = $user;
 *		$this->tpl_data["plain_text_password"] = $plain_text_password;
 * 	}
 * }
 * </code>
 *
 * Example of template:
 * mailer/notify_user_registration.tpl
 * <code>
 * 	Hello {$user->getFullName()},
 *
 *	thanks for signing up for SiliconeWisdom.com!
 *
 * 	Your data revision
 *  login: {$user->getLogin()}
 *	email: {$user->getEmail()}
 *	password: {$plain_text_password}
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
	 * @access protected
	 * @var array
	 * @see Atk14Mailer::add_attachment()
	 */
	var $_attachments = array();

	/**
	 * Template name
	 *
	 * @var string
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
	 */
	var $namespace = null;

	/**
	 * Name of action before current expected
	 *
	 * @var string
	 */
	var $action = null;

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
	 * 	$this->mailer->execute("notify_user_registration",array(
	 *		"user" => $user
	 *	));
	 * </code>
	 *
	 * @param string $action name of action to be executed
	 * @param mixed other optional param
	 * @param mixed other optional param, etc...
	 * @return array sendmail() output
	 */
	function execute(){
		$args = func_get_args();
		call_user_func_array(array($this,"_render_message"),$args);
		return $this->_send();
	}

	/**
	 * Builds a message
	 *
	 * The message will not be sent even in production environment.
	 *
	 * Method is called in a controller.
	 * <code>
	 * 	$mail_ar = $this->mailer->build("notify_user_registration",$user);
	 * </code>
	 *
	 * @param string $action name of action to be build
	 * @param mixed other optional param
	 * @param mixed other optional param, etc...
	 * @return array sendmail() output
	 */
	function build(){
		$args = func_get_args();
		call_user_func_array(array($this,"_render_message"),$args);
		return $this->_send(array("build_message_only" => true));
	}

	function _render_message(){
		global $ATK14_GLOBAL;

		$args = func_get_args();
		$action = array_shift($args);

		$this->body = $this->body_html = ""; // reset body, opetovne volani by NEvyvolalo vygenerovani sablony

		$this->action = $action;
		$this->template_name = $action;

		$this->_before_filter();
		call_user_func_array(array($this,$action),$args); // $this->$action($arg1, $arg2...);

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
	 * @access protected
	 * @return array
	 * @uses sendmail()
	 */
	function _send($params = array()){
		$params += array(
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
			"attachments" => $this->_attachments,
			"build_message_only" => false,
		);
		if($this->body_html){
			// !! experimental feature
			$params["plain"] = $params["body"]; // oups! in sendhtmlmail() there is no param named body
			$params["html"] = $this->body_html;
			unset($params["body"]);
			unset($params["mime_type"]); // mime_type is determined automatically, "multipart/alternative" by default
			$email_ar = sendhtmlmail($params);
		}else{
			$email_ar = sendmail($params);
		}
		if(DEVELOPMENT){
			// logging e-mail data as we are developing
			$this->logger->info(($params["build_message_only"] ? "Building an e-mail (won't be sent in any environment)" : "Sending an e-mail (not for real in DEVELOPMENT)")."\n-----------------------------------------------\nTo: $email_ar[to]\nSubject: $email_ar[subject]\n$email_ar[headers]\n\n$email_ar[body]");
		}
		return $email_ar;
	}
}

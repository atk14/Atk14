<?php
/**
 * Class for sending emails.
 *
 * @filesource
 */

/**
 * Class for sending emails.
 *
 * Atk14Mailer works similar to Atk14Controller. ApplicationMailer class which is descendent of Atk14Mailer contains actions (methods).
 * These actions can be called from a controller by {@link execute()} method with the name of action as parameter.
 *
 * The action prepares data for a template which is associated to it. Data for the template are passed (like in a controller) in $this->tpl_data.
 * The action also sets all important parameters of the email (sender, recipients, subject ...)
 * After the action is executed the email is sent.
 * You can define the {@link _before_filter() _before_filter} method which is called before every action.
 *
 * If the message should not be sent, method build() can be used.
 * It prepares whole message with headers which will be returned by build method() as array.
 *
 * Template is a standard Smarty template. Name of the template is composed of the actions name and .tpl suffix and is stored in directory mailer.
 *
 * This is how an action is called in a controller:
 * ```
 *	$this->mailer->notify_user_registration($user,$password);
 * ```
 *
 * The called action is defined in application_mailer.inc:
 * ```
 *	class ApplicationMailer extends Atk14Mailer {
 *		function notify_user_registration($user,$plain_text_password) {
 *			$this->from = "info@atk14.net";
 *			$this->to = $user->getEmail();
 *			$this->subject = "Welcome to SiliconeWisdom.com";
 *			...
 *			$this->tpl_data["user"] = $user;
 *			$this->tpl_data["plain_text_password"] = $plain_text_password;
 *		}
 *	}
 * ```
 *
 * Example of template:
 * mailer/notify_user_registration.tpl
 * ```
 *	Hello {$user->getFullName()},
 *	 
 * 	thanks for signing up for SiliconeWisdom.com!
 *	 
 *	Your data revision
 *	login: {$user->getLogin()}
 *	email: {$user->getEmail()}
 *	password: {$plain_text_password}
 *	...
 *
 * ```
 * Sending the email
 *
 * ```
 *	class UserController extends ApplicationController {
 *		function register_user() {
 *			$this->mailer->notify_user_registration()
 *		}
 *	}
 * ```
 *
 * Params
 * - from
 * - from _name
 * - return_path
 * - to
 * - cc
 * - bcc
 * - subject
 * - body
 * - mime_type
 * - charset
 * - attachments
 * - build_message_only
 *
 * @package Atk14\Core
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
	var $from_name = ATK14_APPLICATION_NAME; 

	/**
	 * if $return_path is not set, $from is used automatically  as $return_path
	 *
	 * @var string
	 */
	var $return_path = null;

	/**
	 * email address for Reply-To header
	 *
	 * e.g. samantha@doe.com
	 */
	var $reply_to = "";

	/**
	 * name for Reply-To header
	 *
	 * e.g. Samantha Doe
	 */
	var $reply_to_name = "";

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
	 * Render message in a layout?
	 *
	 * Null stands for auto detection.
	 */
	var $render_layout = null;

	/**
	 * Name of the layout.
	 *
	 * e.g. "mailer", "christmas_mailer_theme" (looking for layouts/mailer.tpl, layouts/mailer.html.tpl, layouts/christmas_mailer_theme.tpl, layouts/christmas_mailer_theme.html.tpl)
	 */
	var $layout_name = "";

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
	 * Instance of Logger
	 *
	 * @var Logger
	 */
	var $logger = null;


	private $defaults = array();

	/**
	 * Creates instance of Atk14Mailer depending on a controller.
	 *
	 * The returned class ApplicationMailer must be defined by application in controllers/_namespace_/application_mailer.(inc|php)
	 *
	 * Returns null when the ApplicationMailer class is not defined.
	 *
	 * @param Atk14Controller $controller
	 * @return ApplicationMailer
	 */
	static function GetInstanceByController($controller){
		return Atk14Mailer::GetInstance(array(
			"namespace" => $controller->namespace,
			"logger" => $controller->logger
		));
	}

	/**
	 * Initialization.
	 *
	 * Description of $options
	 * - namespace
	 * - logger
	 *
	 * @param array $options
	 */
	static function GetInstance($options = array()){
		global $ATK14_GLOBAL;
		$options = array_merge(array(
			"namespace" => "",
			"logger" => null
		),$options);

		$namespace = $options["namespace"];
		$logger = isset($options["logger"]) ? $options["logger"] : $ATK14_GLOBAL->getLogger();

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
	 *
	 * ```
	 *	$this->mailer->execute("notify_user_registration",array(
	 *		"user" => $user
	 *	));
	 * ```
	 *
	 *
	 * New form of sending can be used
	 * ```
	 *	$this->mailer->notify_user_registration(array(
	 *		"user" => $user
	 *	));
	 * ```
	 *
	 * @param string $action name of action to be executed
	 * @param mixed other optional param
	 * @param mixed other optional param, etc...
	 * @return array sendmail() output
	 */
	function execute(){
		$this->_save_or_restore_default_state();

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
	 * ```
	 *	$mail_ar = $this->mailer->notify_user_registration($user);
	 * ```
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

	/**
	 * @ignore
	 */
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

			$smarty = $this->_get_smarty(array("assign_data" => false));

			$this->_before_render();
			
			$smarty->assign($this->tpl_data);

			$template_name = $this->template_name.".tpl";
			$html_template_name = $this->template_name.".html.tpl";

			$a_template_found = false;

			if($smarty->templateExists($template_name)){
				$a_template_found = true;
				$this->body = $smarty->fetch($template_name);
				$this->body = $this->_find_and_render_layout($smarty,$this->body);
			}

			if($smarty->templateExists($html_template_name)){
				$a_template_found = true;
				$smarty->clearAtk14Contents(); // there may be atk14 contents from the plain part
				$this->body_html = $smarty->fetch($html_template_name);
				$this->body_html = $this->_find_and_render_layout($smarty,$this->body_html,array("suffix" => ".html"));
			}

			if(!$a_template_found){
				throw new Exception("For mailer ".($this->namespace ? "$this->namespace/" : "").get_class($this)." there is no template $template_name or $html_template_name");
			}

			$this->_after_render();
		}

		$this->_after_filter();
	}

	protected function _get_smarty($options = array()){
		global $ATK14_GLOBAL;

		$options += array(
			"assign_data" => true,
		);

		$namespace = $this->namespace;

		$smarty = Atk14Utils::GetSmarty(array(
			$ATK14_GLOBAL->getApplicationPath()."views/$namespace/mailer/",
			$ATK14_GLOBAL->getApplicationPath()."views/$namespace/",
			$ATK14_GLOBAL->getApplicationPath()."views/",
		),array(
			"namespace" => $namespace,
			"compile_id_salt" => "mailer",
		));

		// environment constants
		$smarty->assign("DEVELOPMENT",DEVELOPMENT);
		$smarty->assign("PRODUCTION",PRODUCTION);
		$smarty->assign("TEST",TEST);

		$smarty->assign("namespace",$this->namespace);
		$smarty->assign("lang",$ATK14_GLOBAL->getLang());
		$smarty->assign("public",$ATK14_GLOBAL->getPublicBaseHref());
		$smarty->assign("root",$ATK14_GLOBAL->getBaseHref());
	
		$options["assign_data"] && $smarty->assign($this->tpl_data);

		return $smarty;
	}

	protected function _find_and_render_layout($smarty,$body,$options = array()){
		global $ATK14_GLOBAL;

		$options += array(
			"suffix" => "", // "" or ".html"
		);

		if(!strlen($body) || $this->render_layout===false){ return $body; }

		$suffix = $options["suffix"];

		$layout_template = "";

		$_layout_name = $this->layout_name ? $this->layout_name : "mailer";
		$_layout_name .= "$suffix.tpl"; // e.g. "mailer.html.tpl"
		foreach(array(
			"$this->namespace/$_layout_name",
			"$_layout_name",
		) as $_path){
			if(file_exists($_p = $ATK14_GLOBAL->getApplicationPath()."layouts/$_path")){
				$layout_template = $_p;
				break;
			}
		}

		if(!$layout_template && $this->render_layout){
			Atk14Utils::ErrorLog("Missing mailer layout template $_layout_name");
			return $body;
		}

		if(!$layout_template){
			return $body;
		}

		$layout_content = $smarty->fetch($layout_template);

		$body = str_replace("<%atk14_content[main]%>",$body,$layout_content);
		foreach($smarty->getAtk14ContentKeys() as $c_key){
			$body = str_replace("<%atk14_content[$c_key]%>",$smarty->getAtk14Content($c_key),$body);
		}

		return $body;
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
	 * This method is called after every action in ApplicationMailer
	 *
	 * @access protected
	 */
	function _after_filter(){ }

	/**
	 * Calls sendmail function and pass it all important fields to construct the message and send it.
	 *
	 * Params
	 * - from
	 * - from _name
	 * - return_path
	 * - to
	 * - cc
	 * - bcc
	 * - subject
	 * - body
	 * - mime_type
	 * - charset
	 * - attachments
	 * - build_message_only
	 *
	 * @param array $params
	 * @return array
	 * @uses sendmail()
	 * @ignore
	 */
	protected function _send($params = array()){
		$params += array(
			"from" => $this->from,
			"from_name" => $this->from_name,
			"return_path" => $this->return_path,
			"reply_to" => $this->reply_to,
			"reply_to_name" => $this->reply_to_name,
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
		if(strlen($this->body_html) && strlen($this->body)){
			// !! experimental feature
			$params["plain"] = $params["body"]; // oups! in sendhtmlmail() there is no param named body
			$params["html"] = $this->body_html;
			unset($params["body"]);
			unset($params["mime_type"]); // mime_type is determined automatically, "multipart/alternative" by default
			$email_ar = sendhtmlmail($params);
		}else{
			if(strlen($this->body_html)){
				$params["body"] = $this->body_html;
				$params["mime_type"] = "text/html";
			}
			$email_ar = sendmail($params);
		}
		if(DEVELOPMENT){
			// Logging email data as we are developing
			$message = "To: $email_ar[to]\nSubject: $email_ar[subject]\n$email_ar[headers]\n\n$email_ar[body]";
			$this->logger->info(($params["build_message_only"] ? "Building an email (won't be sent in any environment)" : "Sending an email (not for real in DEVELOPMENT)")."\n-----------------------------------------------\n$message");

			// Saving email into a file in TEMP/sent_emails/
			// To:, Cc: and Bcc: headers they will be overwritten
			$headers = "X-Original-To: $email_ar[to]\nSubject: $email_ar[subject]\n$email_ar[headers]";
			$headers = preg_replace('/\nCc:/is',"\nX-Original-Cc:",$headers);
			$headers = preg_replace('/\nBcc:/is',"\nX-Original-Bcc:",$headers);
			//$headers = "To: ".ATK14_ADMIN_EMAIL."\n".$headers; // adding new To: address
			$message = "$headers\n\n$email_ar[body]";

			$dir = TEMP."/sent_emails";
			$filename = date("Y-m-d_H_i_s")."_".(uniqid()).".eml";
			Files::MkDir($dir);
			Files::WriteToFile("$dir/$filename",$message);

			// (Re)creating symlink latest
			if(file_exists("$dir/latest")){ unlink("$dir/latest"); }
			symlink("$filename","$dir/latest");

			//$this->logger->info(
			//	"You can send the message to yourself by typing this command in shell:\n".
			//	sprintf('{ echo "To: %s"; cat %s; } | mutt -H -',ATK14_ADMIN_EMAIL,"$dir/$filename")
			//);
			$this->logger->info(
				"You can send the last message to yourself by typing this command in shell:\n".
				sprintf('cat %s | sendmail -f "%s" "%s"',"$dir/latest",$this->from,ATK14_ADMIN_EMAIL)
			);
		}
		return $email_ar;
	}

	/**
	 * Generates a URL
	 *
	 * It's nearly the same as in a controller
	 */
	function _link_to($params = array(), $options = array()){
		$options += array(
			"connector" => "&",
			"with_hostname" => true, // in emails the hostname is expected in URLs by default
		);
		return Atk14Url::BuildLink($params,$options);
	}

	/**
	 * Resets the mailer to the default state
	 * 
	 */
	private function _save_or_restore_default_state(){
		if(!$this->defaults){
			// saving
			$vars = get_object_vars($this);
			unset($vars["defaults"]);
			$this->defaults = $vars;
			return;
		}

		// restoring
		foreach($this->defaults as $key => $value){
			$this->$key = $value;
		}
	}
}

<?
/**
 * Class for sending emails.
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
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
	 * @var string
	 */
	var $from = "atk14@mailer.cz";

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
	 * Content charset. Defaults to 'UTF-8'
	 *
	 * @var string
	 */
	var $content_charset = "UTF-8";

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
	 * Creates instance of Atk14Mailer depending on a controller.
	 *
	 * The returned class ApplicationMailer must be defined by application in controllers/_namespace_/application_mailer.inc
	 *
	 * Returns null when the ApplicationMailer class is not defined.
	 *
	 * @param Atk14Controller $controller
	 * @return ApplicationMailer
	 * @static
	 */
	function GetInstanceByController($controller){
		$out = null;
		if(Atk14Require::Load("controllers/$controller->namespace/application_mailer.inc")){
			$out = new ApplicationMailer();
			$out->namespace = $controller->namespace;
		}

		return $out;
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
	 */
	function clear_attachments(){ $this->_attachments = array(); }


	/**
	 * This method is called before every action in ApplicationMailer
	 *
	 * zatim jen _before_filter,
	 * myslim, ze nic dalsiho nebude treba
	 *
	 * @access protected
	 */
	function _before_filter(){ }

	/**
	 * Calls sendmail function and pass it all important fields to construct the message and send it.
	 *
	 * @access private
	 * @return array
	 * @uses sendmail()
	 *
	 */
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

<?php
/**
 * Class for better manipulation with Atk14Mailer
 *
 * @package Atk14
 * @subpackage Core
 * @author Jaromir Tomek
 * @filesource
 */

class Atk14MailerProxy{
	/**
	 *
	 * @var Atk14Mailer
	 */
	private $_mailer;

	function __construct($mailer){
		$this->_mailer = $mailer;
	}

	static function GetInstanceByController($controller){
		if($mailer = Atk14Mailer::GetInstanceByController($controller)){
			return new Atk14MailerProxy($mailer);
		}
	}

	static function GetInstance($options = array()){
		if($mailer = Atk14Mailer::GetInstance($options)){
			return new Atk14MailerProxy($mailer);
		}
	}

	function __call($method,$arguments){
		$methods_to_proxy = array(
			"execute",
			"build",
			"add_attachment",
			"clear_attachments",
			"add_html_image",
			"clear_html_images",
		);
		if(in_array($method,$methods_to_proxy) || preg_match('/^_/',$method)){
			$callable = array($this->_mailer,$method);
		}else{
			// $proxy->notify_user_registration($user) is treated as $this->_mailer->execute("notify_user_registration",$user);
			array_unshift($arguments,$method);
			$callable = array($this->_mailer,"execute");
		}

		return call_user_func_array($callable,$arguments);
	}

	function __get($name){
		return $this->_mailer->$name;
	}

	function __set($name,$value){
		$this->_mailer->$name = $value;
	}
}

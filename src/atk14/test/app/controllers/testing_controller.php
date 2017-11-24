<?php
class TestingController extends ApplicationController{
	function test(){
		$this->tpl_data["value_assigned_from_action_method"] = "there_is_a_value_assigned_from_action_method";
	}

	function test_render(){
		$this->tpl_data["firstname"] = "John";
		$this->snippet = $this->tpl_data["snippet"] = $this->_render("shared/user_detail",array(
			"lastname" => "Doe"
		));
	}

	function test_render_collection(){
		$this->tpl_data["articles"] = array(
			array(
				"title" => "First article",
				"teaser" => "Teaser of the first article",
			),
			array(
				"title" => "Second article",
				"teaser" => "Teaser of the second article",
			),
		);
	}

	function default_layout(){
		$this->template_name = "template";
	}

	function no_layout(){
		$this->template_name = "template";
		$this->render_layout = false;
	}

	function custom_layout(){
		$this->template_name = "template";
		$this->layout_name = "custom";
	}

	function custom_layout_set_from_template(){
	}

	function send_ordinary_mail(){
		$this->render_template = false;
		$this->mail_ar = $this->mailer->execute("ordinary_notification","ORIGINAL_WAY");
	}

	function send_ordinary_mail_new_way(){
		$this->render_template = false;
		$this->mail_ar = $this->mailer->ordinary_notification("NEW_WAY");
	}

	function send_html_mail(){
		$this->render_template = false;
		$this->mail_ar = $this->mailer->execute("html_notification");
	}

	function send_html_mail_without_layout(){
		$this->render_template = false;
		$this->mail_ar = $this->mailer->execute("html_notification_without_layout");
	}

	function send_html_mail_christmas_theme(){
		$this->render_template = false;
		$this->mail_ar = $this->mailer->execute("html_notification_christmas_theme");
	}

	function testing_hooks(){
		$this->render_template = false;
		$this->mail_ar = $this->mailer->execute("testing_hooks");
	}

	function send_user_data_summary(){
		$this->render_template = false;
		$this->mail_ar = $this->mailer->execute("user_data_summary_notification","john.doe","john@doe.com","krefERE34");
	}

	function test_email_rendering(){
		$this->render_template = false;
		$this->mail_ar = $this->mailer->test_rendering();
	}

	function cookies_dumper(){
		$this->render_template = false;
		$this->response->write(var_export($this->request->getCookieVars(),true));
	}

	function set_cookie(){
		$this->render_template = false;
		$this->response->addCookie(new HTTPCookie("user_name","John Doe"));
	}

	function test_caching(){
		$this->response->setContentCharset("utf-8");
		$this->tpl_data["random_value"] = uniqid();
	}

	function test_caching_without_template(){
		$this->render_template = false;
		$this->response->setContentType("text/plain");
		$this->response->setContentCharset("us-ascii");
		$this->response->setStatusCode(222);
		$this->response->write("random_value: ".uniqid());
	}

	function test_caching_with_layout_set_in_action(){
		$this->tpl_data["random_value"] = uniqid();
	}

	function _before_filter(){
		if(!$this->params->defined("disable_cache")){
			$this->_caches_action(array(
				"action" => array("test_caching","test_caching_without_template","test_caching_with_layout_set_in_action"),
				"salt" => $this->params["alt"],
			));
		}
	}

	function _before_render(){
		$this->smarty->assign("value_assigned_directly_from_before_render","there_is_a_value_assigned_directly_from_before_render");
		$this->tpl_data["value_assigned_usually_from_before_render"] = "there_is_a_value_assigned_usually_from_before_render";
	}
}

<?php
class TcMailer extends TcBase{
	function test_loading(){
		$controller = $this->client->get("en/testing/test");
		$this->assertEquals("",$controller->namespace);
		$this->assertEquals("",$controller->mailer->namespace);

		// in namespace admin there is a mailer
		$controller = $this->client->get("admin/en/main/index");
		$this->assertEquals("admin",$controller->namespace);
		$this->assertEquals("admin",$controller->mailer->namespace);

		// in namespace universe there is no mailer, the default one is used instead
		$controller = $this->client->get("universe/en/planets/index");
		$this->assertEquals("universe",$controller->namespace);
		$this->assertEquals("",$controller->mailer->namespace);
	}

	function test_sending(){
		$controller = $this->client->get("testing/send_ordinary_mail");
		$this->assertContains("this is just an ordinary notification from tests",$controller->mail_ar["body"]);
		$this->assertContains("way: ORIGINAL_WAY",$controller->mail_ar["body"]);
		$this->assertEquals("unit@testing.com",$controller->mail_ar["from"]);
		$this->assertContains('From: "Unit Testing" <unit@testing.com>',$controller->mail_ar["headers"]);
		$this->assertContains("Content-Type: text/plain",$controller->mail_ar["headers"]);

		$controller = $this->client->get("testing/send_ordinary_mail_new_way");
		$this->assertContains("this is just an ordinary notification from tests",$controller->mail_ar["body"]);
		$this->assertContains("way: NEW_WAY",$controller->mail_ar["body"]);
		$this->assertEquals("unit@testing.com",$controller->mail_ar["from"]);
		$this->assertContains('From: "Unit Testing" <unit@testing.com>',$controller->mail_ar["headers"]);
		$this->assertContains("Content-Type: text/plain",$controller->mail_ar["headers"]);

		$controller = $this->client->get("testing/send_html_mail");
		$this->assertEquals("unit@testing.com",$controller->mail_ar["from"]);
		$this->assertContains('From: "Unit Testing" <unit@testing.com>',$controller->mail_ar["headers"]);
		$this->assertContains("Content-Type: multipart/related",$controller->mail_ar["headers"]);
		$this->assertContains("The plain part",$controller->mail_ar["body"]);
		$this->assertTrue(!!preg_match($_pattern_plain = '/Dear Customer\s+The plain part/s',$controller->mail_ar["body"])); // app/layouts/mailer.tpl
		$this->assertTrue(!!preg_match($_pattern_html = '/<h2>Dear Customer<\/h2>.+<p>The rich part<\/p>.+Best Regards<br>\s*<b>SnakeOil ltd<\/b>/s',$controller->mail_ar["body"])); // app/layouts/mailer.html.tpl

		// template for plain text part doesn't exist in this case
		$controller = $this->client->get("testing/send_html_only_mail");
		$this->assertEquals("unit@testing.com",$controller->mail_ar["from"]);
		$this->assertEquals("HTML only notification",$controller->mail_ar["subject"]);
		$this->assertContains('From: "Unit Testing" <unit@testing.com>',$controller->mail_ar["headers"]);
		$this->assertContains("Content-Type: text/html",$controller->mail_ar["headers"]);
		$this->assertTrue(!!preg_match($_pattern_html = '/<h2>Dear Customer<\/h2>.+<p>The HTML only message<\/p>.+Best Regards<br>\s*<b>SnakeOil ltd<\/b>/s',$controller->mail_ar["body"])); // app/layouts/mailer.html.tpl
		$this->assertTrue(!isset($controller->mail_ar["body_html"]));

		// layout is not rendered
		$controller = $this->client->get("testing/send_html_mail_without_layout");
		$this->assertContains("The plain part",$controller->mail_ar["body"]);
		$this->assertContains("<p>The rich part</p>",$controller->mail_ar["body"]);
		$this->assertFalse(!!preg_match($_pattern_plain,$controller->mail_ar["body"]));
		$this->assertFalse(!!preg_match($_pattern_html,$controller->mail_ar["body"]));

		// layout with christmas theme
		$controller = $this->client->get("testing/send_html_mail_christmas_theme");
		$this->assertTrue(!!preg_match('/<h2>Dear Customer<\/h2>.+<p>The rich part<\/p>.+Merry Christmas<br>\s*<b>SnakeOil ltd<\/b>/s',$controller->mail_ar["body"]));
		$this->assertTrue(!!preg_match('/Dear Customer\s+The plain part\s+Merry Christmas\s+SnakeOil/s',$controller->mail_ar["body"])); // app/layouts/mailer/tpl

		$exception_msg = "";
		try{
			$controller = $this->client->get("testing/send_mail_without_template");
		}catch(Exception $e){
			$exception_msg = $e->getMessage();
		}
		$this->assertEquals("For mailer ApplicationMailer there is no template notification_without_templates.tpl or notification_without_templates.html.tpl",$exception_msg);

		// TODO: decode email bodies
	}

	function test_rendering(){
		$controller = $this->client->get("en/testing/test_email_rendering");
		$body = $controller->mail_ar["body"];
		$this->assertContains("Hello, this is email for rendering test",$body);
		$this->assertContains("lang: en",$body);
		$this->assertContains("environment: TEST",$body);
	}

	function testing_hooks(){
		$controller = $this->client->get("testing/testing_hooks");

		$this->assertContains("_before_filter: OK (bf)",$controller->mail_ar["body"]);
		$this->assertContains("_before_render: OK (br)",$controller->mail_ar["body"]);
		$this->assertContains("_after_render: OK (ar)",$controller->mail_ar["body"]);
	}

	function testing_params_passing(){
		$controller = $this->client->get("testing/send_user_data_summary");
		$this->assertEquals("john@doe.com",$controller->mail_ar["to"]);
		$this->assertContains("login: john.doe",$controller->mail_ar["body"]);
		$this->assertContains("email: john@doe.com",$controller->mail_ar["body"]);
		$this->assertContains("password: krefERE34",$controller->mail_ar["body"]);
	}

	function test_resetting_to_default_state(){
		$controller = $this->client->get("testing/test");
		$mailer = $controller->mailer;

		// inside the method send_user_data_summary() the $render_layout is set to false
		$mail = $mailer->simple_message_without_layout();
		$this->assertEquals("Hello from simple message!",trim($mail["body"]));
		$this->assertEquals("big@brother.com",$mail["cc"]);

		// now it is expected that the layout should be automatically rendered
		$mail = $mailer->ordinary_notification();
		$this->assertContains("Best Regards",$mail["body"]); // a string from the layout
		$this->assertEquals("",$mail["cc"]);
	}
}

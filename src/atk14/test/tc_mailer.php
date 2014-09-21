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
		$this->assertContains("<p>The rich part</p>",$controller->mail_ar["body"]);

		// TODO: decode email bodies
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
}

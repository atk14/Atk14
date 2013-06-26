<?
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
		$this->assertEquals("unit@testing.com",$controller->mail_ar["from"]);
		$this->assertContains("From: Unit Testing <unit@testing.com>",$controller->mail_ar["headers"]);
		$this->assertContains("Content-Type: text/plain",$controller->mail_ar["headers"]);

		$controller = $this->client->get("testing/send_html_mail");
		$this->assertEquals("unit@testing.com",$controller->mail_ar["from"]);
		$this->assertContains("From: Unit Testing <unit@testing.com>",$controller->mail_ar["headers"]);
		$this->assertContains("Content-Type: multipart/related",$controller->mail_ar["headers"]);
		$this->assertContains("The plain part",$controller->mail_ar["body"]);
		$this->assertContains("<p>The rich part</p>",$controller->mail_ar["body"]);

		// TODO: decode email bodies
	}
}

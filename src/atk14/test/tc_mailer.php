<?
class TcMailer extends TcBase{
	function test_load(){
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
}

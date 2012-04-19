<?php
class TcCreatures extends TcBase{

	function test_formats(){
		$client = $this->client;

		$creature = Creature::CreateNewRecord(array(
			"name" => "Brand NewCreature",
		));

		// no format
		$client->get("creatures/detail",array(
			"id" => $creature
		));
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("text/html",$client->getContentType());

		// xml format
		$client->get("creatures/detail",array(
			"id" => $creature,
			"format" => "xml"
		));
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("text/xml",$client->getContentType());
		$this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>'."\n".$creature->toXml(),$client->getContent());

		// json format
		$client->get("creatures/detail",array(
			"id" => $creature,
			"format" => "json"
		));
		$this->assertEquals(200,$client->getStatusCode());
		$this->assertEquals("text/plain",$client->getContentType());
		$this->assertEquals($creature->toJson(),$client->getContent());

		// silly format
		$client->get("creatures/detail",array(
			"id" => $creature->getId(),
			"format" => "bmp"
		));
		$this->assertEquals(404,$client->getStatusCode());
	}
}

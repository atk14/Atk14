<?php
/**
 *
 * @fixture users
 * @fixture articles
 */
class TcFixtures extends TcAtk14Model {

	function test(){
		$john = $this->users["john"];
		$this->assertTrue(is_object($john));
		$this->assertEquals("john.doe",$john->getLogin());

		$samantha = $this->users["samantha"];
		$this->assertTrue(is_object($samantha));
		$this->assertEquals("samantha.doe",$samantha->getLogin());

		$article1 = $this->articles["article1"];
		$this->assertEquals("Title 1",$article1->getTitle());
		$this->assertEquals("Body 1",$article1->getBody());
		$this->assertEquals($samantha->getId(),$article1->getAuthorId());

		$article2 = $this->articles["article2"];
		$this->assertEquals("Title 2",$article2->getTitle());
		$this->assertEquals("Body 2",$article2->getBody());
		$this->assertEquals($john->getId(),$article2->getAuthorId());
	}
}

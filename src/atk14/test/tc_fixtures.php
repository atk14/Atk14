<?php
/**
 *
 * @fixture users
 * @fixture articles
 * @fixture test_table
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

		// There is no model class for records in test_table,
		// so $this->test_table is an array of arrays

		$rec_1 = $this->test_table["rec_1"];
		$this->assertTrue(is_array($rec_1));
		$this->assertEquals(array("an_integer" => "123", "title" => "Wonderful World"),$rec_1);

		$rec_2 = $this->test_table["rec_2"];
		$this->assertTrue(is_array($rec_2));
		$this->assertEquals(array("title" => "Some nice title"),$rec_2);
	}
}

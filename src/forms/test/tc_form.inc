<?php
class tc_form extends tc_base{
	function test_disable_field(){
		$f = new Form();
		$f->add_field("firstname",new CharField(array()));
		$f->add_field("lasttname",new CharField(array()));

		$this->assertEquals(2,sizeof($f->fields));
		$this->assertEquals(false,$f->fields["firstname"]->disabled);
		$this->assertEquals(false,$f->fields["lasttname"]->disabled);

		$f->disable_fields(array("firstname","title")); // !! there is no a title field

		$this->assertEquals(2,sizeof($f->fields));
		$this->assertEquals(true,$f->fields["firstname"]->disabled);
		$this->assertEquals(false,$f->fields["lasttname"]->disabled);

		$f->disable_fields(array("lasttname"));

		$this->assertEquals(2,sizeof($f->fields));
		$this->assertEquals(true,$f->fields["firstname"]->disabled);
		$this->assertEquals(true,$f->fields["lasttname"]->disabled);
	}

	function test_is_multipart(){
		$f = new Form();
		$this->assertEquals(false,$f->is_multipart());

		$f->add_field("firstname",new CharField(array()));
		$this->assertEquals(false,$f->is_multipart());

		$f->add_field("picture",new ImageField(array()));
		$this->assertEquals(true,$f->is_multipart());
	}
}


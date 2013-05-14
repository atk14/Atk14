<?php
class TcForm extends TcBase{
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

	function test_text_conversion_on_field(){
		$f = new Form();
		$f->add_field("firstname",new CharField(array()));
		$field = $f->get_field("firstname");
		$this->assertEquals($field->as_widget(),"$field");
	}

	function test_array_access(){
		$f = new Form();
		$this->assertEquals(null,$f["firstname"]);

		$f["firstname"] = new CharField(array());
		$this->assertEquals(true,is_object($f["firstname"]));

		$f["lasttname"] = new CharField(array());
		$this->assertEquals(array("firstname","lasttname"),$f->get_field_keys());

		$f["lasttname"] = null;
		$this->assertEquals(array("firstname"),$f->get_field_keys());

		unset($f["firstname"]);
		$this->assertEquals(array(),$f->get_field_keys());
	}
}

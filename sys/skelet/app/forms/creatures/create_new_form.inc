<?
class CreateNewForm extends ApplicationForm{
	function set_up(){
		$this->add_field("name", new CharField(array(
			"label" => _("Name"),
			"max_length" => 255,
		)));

		$this->add_field("description", new CharField(array(
			"label" => _("Description"),
			"required" => false,
			"widget" => new TextArea(),
		)));

		$this->add_field("image_url", new CharField(array(
			"label" => _("Image URL"),
			"required" => false,
			"null_empty_output" => true,
		)));
	}
}

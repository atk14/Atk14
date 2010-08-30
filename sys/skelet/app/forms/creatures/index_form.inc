<?
class IndexForm extends ApplicationForm{
	function set_up(){
		$this->set_method("get");

		$this->add_field("q",new CharField(array(
			"label" => _("Search term"),
			"required" => false,
			"max_length" => 100,
		)));
	}
}

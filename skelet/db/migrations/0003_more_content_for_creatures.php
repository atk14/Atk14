<?
class MoreContentForCreatures extends Atk14Migration{
	function up(){
		$data_ar = array(
			array(
				"name" => "Second creature",
				"description" => "Normal creature. No picture is needed."
			),
			array(
				"name" => "Third creature",
				"description" => "Yet another creature."
			)
		);

		foreach($data_ar as $data){
			Creature::CreateNewRecord($data);
		}
	}
}

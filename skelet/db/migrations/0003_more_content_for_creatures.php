<?php
class MoreContentForCreatures extends Atk14Migration{
	function up(){
		if(!class_exists("Creature")){
			// This migration depends on a sample class Creature.
			// This condition prevents the migration to fail when you get the class Creature out of the project.
			return;
		}

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

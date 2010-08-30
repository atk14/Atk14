<?
class Creature extends Model{
	function Creature(){
		parent::Model("creatures");
	}

	function GetInstanceById($id){
		return Model::_GetInstanceById("Creature",$id);
	}

	function CreateNewRecord($values,$options = array()){
		return Model::_CreateNewRecord("Creature",$values,$options);
	}

	function getName(){ return $this->g("name"); }
	function getDescription(){ return $this->g("description"); }

	function getImageUrl(){ return $this->g("image_url"); }
	function hasImage(){ return strlen($this->getImageUrl())>0; }
}

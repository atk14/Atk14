<?
class Creature extends ApplicationModel{
	function __construct(){
		parent::__construct("creatures");
	}

	function getName(){ return $this->g("name"); }
	function getDescription(){ return $this->g("description"); }

	function getImageUrl(){ return $this->g("image_url"); }
	function hasImage(){ return strlen($this->getImageUrl())>0; }
}

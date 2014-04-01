<?php
class FablesRouter extends Atk14Router{
	static $SLUGS = array(
		"1" => "green-eggs-and-ham",
		"3" => "the-cat-in-the-hat",
		"5" => "where-the-wild-things-are",
	);

	// /fable/some-title-123 -> /en/fables/detail/?id=123
	function recognize($uri){
		if(preg_match('/^\/(fable|bajka)\/[a-z0-9-]+-(\d+)$/',$uri,$matches)){
			$this->controller = "fables";
			$this->action = "detail";
			$this->lang = $matches[1]=="fable" ? "en" : "cs";
			$this->params->add("id",$matches[2]);
		}
	}

	// /en/fables/detail/?id=123 -> /fable/some-title-123
	function build(){
		if($this->controller!="fables" || !in_array($this->lang,array("cs","en"))){ return; }

		if($this->action=="detail" && is_numeric($id = $this->params->g("id"))){
			$slug = isset(static::$SLUGS[$id]) ? static::$SLUGS[$id] : "a-very-good-fable";
			$fable = $this->lang=="en" ? "fable" : "bajka";
			$this->params->del("id");
			return "/$fable/$slug-$id";
		}
	}
}

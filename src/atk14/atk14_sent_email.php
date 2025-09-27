<?php
#[\AllowDynamicProperties]
class Atk14SentEmail {

	function __construct(array $params){
		$this->body = "";
		$this->body_html = "";

		foreach($params as $key => $value){
			$this->$key = $value; // $this->to, $this->from, $this->subject...
		}

		$this->content_type = isset($params["mime_type"]) ? $params["mime_type"] : "text/plain";
		$this->content_charset = $params["charset"];

		if(!isset($params["body"])){
			$this->body = $params["plain"];
			$this->body_html = $params["html"];
		}
	}
}

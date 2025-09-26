<?php
#[\AllowDynamicProperties]
class Atk14SentEmail {

	function __construct(array $params){
		foreach($params as $key => $value){
			$this->$key = $value; // $this->to, $this->from, $this->subject...
		}
	}
}

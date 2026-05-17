<?php
class miniYAML_proxy extends miniYAML {

	function cutOutBlock($start_at,$indent,$lines = null){
		return $this->_cutOutBlock($start_at,$indent,$lines);
	}

	function cutOutBlock_Stripped($start_at,$indent,$lines = null){
		return $this->_cutOutBlock_Stripped($start_at,$indent,$lines);
	}
}

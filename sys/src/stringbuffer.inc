<?
class StringBuffer{
	var $_Buffer = array();
	
	/**
	* Konstruktor.
	* Jako volitelny parametr je mozne predat string, ktery se hned vlozi do instance.
	* 
	* @access public
	* @param string $string_to_add
	*/
	function StringBuffer($string_to_add = ""){
		settype($string_to_add,"string");
		if(strlen($string_to_add)>0){
			$this->AddString($string_to_add);
		}
	}

	function toString(){
		return join("",$this->_Buffer);
	}
	
	function AddString($string_to_add){
		settype($string_to_add,"string");
		if(strlen($string_to_add)>0){
			$this->_Buffer[] = $string_to_add;
		}
	}
	function AddStringBuffer($stringbuffer_to_add){
		if(!isset($stringbuffer_to_add)){ return;}
		for($i=0;$i<sizeof($stringbuffer_to_add->_Buffer);$i++){
			$this->_Buffer[] = $stringbuffer_to_add->_Buffer[$i];
		}
	}
	function getLength(){
		$out = 0;
		for($i=0;$i<sizeof($this->_Buffer);$i++){
			$out = $out + strlen($this->_Buffer[$i]);
		}
		return $out;
	}
	function PrintOut(){
		for($i=0;$i<sizeof($this->_Buffer);$i++){
			echo $this->_Buffer[$i];
		}
	}
	function Clear(){
		$this->_Buffer = array();
	}

	/**
	* Nahradi v bufferu string jinym stringem.
	* 
	* @access public
	* @param string $search
	* @param string $replace			nebo to muze byt dalsi StringBuffer
	*	@return null
	*/
	function Replace($search,$replace){
		settype($search,"string");

		// prevod StringBuffer na string
		if(is_object($replace)){
			$replace = $replace->toString();
		}

		for($i=0;$i<sizeof($this->_Buffer);$i++){
			$this->_Buffer[$i] = str_replace($search,$replace,$this->_Buffer[$i]);
		}
	}
}
?>

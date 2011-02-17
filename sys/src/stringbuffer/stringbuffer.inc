<?
/**
 * Class provides operations with strings.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */

/**
 * Class provides operations with strings.
 *
 * Internally the class holds its content in array of strings as they were added.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class StringBuffer{

	/**
	 * @access private
	 */
	var $_Buffer = array();
	
	/**
	 * Creates new instance of StringBuffer.
	 *
	 * By default it creates an instance with empty buffer. Optionally you can pass a string to begin with.
	 * @param string $string_to_add
	 */
	function StringBuffer($string_to_add = ""){
		settype($string_to_add,"string");
		if(strlen($string_to_add)>0){
			$this->AddString($string_to_add);
		}
	}

	/**
	 * Returns content of the buffer.
	 *
	 * @return string
	 */
	function toString(){
		return join("",$this->_Buffer);
	}
	
	/**
	 * Adds another string to the buffer.
	 *
	 * @param string $string_to_add
	 */
	function AddString($string_to_add){
		settype($string_to_add,"string");
		if(strlen($string_to_add)>0){
			$this->_Buffer[] = $string_to_add;
		}
	}

	/**
	 * Adds content of another StringBuffer to the buffer.
	 *
	 * @param StringBuffer $stringbuffer_to_add
	 */
	function AddStringBuffer($stringbuffer_to_add){
		if(!isset($stringbuffer_to_add)){ return;}
		for($i=0;$i<sizeof($stringbuffer_to_add->_Buffer);$i++){
			$this->_Buffer[] = $stringbuffer_to_add->_Buffer[$i];
		}
	}

	/**
	 * Returns length of buffer content.
	 *
	 * @return integer
	 */
	function getLength(){
		$out = 0;
		for($i=0;$i<sizeof($this->_Buffer);$i++){
			$out = $out + strlen($this->_Buffer[$i]);
		}
		return $out;
	}

	/**
	 * Echoes content of buffer.
	 */
	function PrintOut(){
		for($i=0;$i<sizeof($this->_Buffer);$i++){
			echo $this->_Buffer[$i];
		}
	}

	/**
	 * Clears buffer.
	 */
	function Clear(){
		$this->_Buffer = array();
	}

	/**
	 * Replaces string in buffer with replacement string.
	 *
	 * @access public
	 *
	 * @param string $search replaced string
	 * @param string|StringBuffer $replace	replacement string. or another StringBuffer object
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

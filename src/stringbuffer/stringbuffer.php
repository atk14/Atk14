<?php
/**
 * Class provides operations with string buffering.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 */

/**
 * Class provides operations with string buffering.
 *
 * Internally the class holds its content in array of strings as they were added.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class StringBuffer{

	/**
	 * Buffer for storing content.
	 * @access private
	 */
	var $_Buffer = array();
	
	/**
	 * Creates new instance of StringBuffer.
	 *
	 * By default it creates an instance with empty buffer. Optionally you can pass a string to begin with.
	 * @param string $string_to_add
	 */
	function __construct($string_to_add = ""){
		settype($string_to_add,"string");
		if(strlen($string_to_add)>0){
			$this->addString($string_to_add);
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
	 * echo "$buffer"; // same as echo $buffer->toString()
	 */
	function __toString(){
		return $this->toString();
	}
	
	/**
	 * Adds another string to the buffer.
	 *
	 * @param string $string_to_add
	 */
	function addString($string_to_add){
		settype($string_to_add,"string");
		if(strlen($string_to_add)>0){
			$this->_Buffer[] = new StringBufferItem($string_to_add);
		}
	}

	/**
	 * Add content of the given file to buffers
	 *
	 * $buffer->addFile("/path/to/file");
	 *
	 * @param string $filename
	 */
	function addFile($filename){
		$this->_Buffer[] = new StringBufferFileItem($filename);
	}

	/**
	 * Adds content of another StringBuffer to the buffer.
	 *
	 * @param StringBuffer $stringbuffer_to_add
	 */
	function addStringBuffer($stringbuffer_to_add){
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
			$out = $out + $this->_Buffer[$i]->getLength();
		}
		return $out;
	}

	/**
	 * Echoes content of buffer.
	 */
	function printOut(){
		for($i=0;$i<sizeof($this->_Buffer);$i++){
			$this->_Buffer[$i]->flush();
		}
	}

	/**
	 * Clears buffer.
	 */
	function clear(){
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
	function replace($search,$replace){
		settype($search,"string");

		// prevod StringBuffer na string
		if(is_object($replace)){
			$replace = $replace->toString();
		}

		for($i=0;$i<sizeof($this->_Buffer);$i++){
			$this->_Buffer[$i]->replace($search,$replace);
		}
	}
}

class StringBufferItem{
	function __construct($string){
		$this->_String = $string;
	}

	function getLength(){ return strlen($this->_String); }
	function flush(){ echo $this->_String; }
	function toString(){ return $this->_String; }
	function __toString(){ return $this->toString(); }

	function replace($search,$replace){
		$this->_String = str_replace($search,$replace,$this->_String);
	}
}

class StringBufferFileItem extends StringBufferItem{
	function __construct($filename){
		$this->_Filename = $filename;
	}

	function getLength(){
		if(isset($this->_String)){ return parent::getLength(); }
		return filesize($this->_Filename);
	}

	function flush(){
		if(isset($this->_String)){ return parent::flush(); }
		readfile($this->_Filename);
	}

	function toString(){
		if(isset($this->_String)){ return parent::toString(); }
		return Files::GetFileContent($this->_Filename);
	}

	function replace($search,$replace){
		$this->_String = $this->toString();
		return parent::replace($search,$replace);
	}
}

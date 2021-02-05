<?php
/**
 * Class provides operations with string buffering.
 *
 * @filesource
 */

/**
 * Class provides operations with string buffering.
 *
 * Internally the class holds its content in array of strings as they were added.
 *
 * @package Atk14\StringBuffer
 */
class StringBuffer{

	/**
	 * Buffer for storing content.
	 *
	 * @ignore
	 * @var array
	 */
	private $_Buffer = array();
	
	/**
	 * Creates new instance of StringBuffer.
	 *
	 * By default it creates an instance with empty buffer. Optionally you can pass a string to begin with.
	 *
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
	 * Returns string representation of the object.
	 *
	 * This will output 'Something in buffer'
	 * 	$buffer = new StringBuffer("Something in buffer");
	 * 	echo "$buffer";
	 *
	 * @return string
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

	/**
	 * Returns the portion of buffered string specified by the offset and length parameters
	 *
	 *	$part = $buffer->substr(10,20);
	 */
	function substr($offset,$length = null){
		if($offset<0){
			$offset = $this->getLength() - abs($offset);
			if($offset<0){
				// $length = is_null($length) ? $length : $length - abs($offset);
				$offset = 0;
			}
		}

		$out = "";
		foreach($this->_Buffer as $b){
			if(!is_null($length) && $length<=0){
				break;
			}
			$b_length = $b->getLength();
			if($offset>$b_length-1){
				$offset = $offset-$b_length;
				continue;
			}
			$out .= $b->substr($offset,$length);
			if(!is_null($length)){
				$bytes_taken = min($length,$b_length - $offset);
				$length = $length - $bytes_taken;
			}
			$offset = 0;
		}
		return $out;
	}
}

/**
 * Element to be added to StringBuffer as string
 *
 * @package Atk14\StringBuffer
 */
class StringBufferItem{

	/**
	 * Initializes file buffer element
	 *
	 * @param string $string
	 */
	function __construct($string){
		$this->_String = $string;
	}

	/**
	 * Returns length of string in buffer.
	 *
	 * @return int
	 */
	function getLength(){ return strlen($this->_String); }
	function flush(){ echo $this->_String; }

	/**
	 * Returns string representation of the object.
	 *
	 * @return string
	 */
	function toString(){ return $this->_String; }

	/**
	 * Method that returns string representation of the object.
	 */
	function __toString(){ return $this->toString(); }

	/**
	 * Replace part of string in buffer
	 *
	 * @param string $search
	 * @param string $replace
	 */
	function replace($search,$replace){
		$this->_String = str_replace($search,$replace,$this->_String);
	}

	function substr($offset,$length = null){
		if(is_null($length)){
			return substr($this->_String,$offset);
		}
		return substr($this->_String,$offset,$length);
	}
}

/**
 * Element to be added to StringBuffer as file
 *
 * @package Atk14\StringBuffer
 */
class StringBufferFileItem extends StringBufferItem{

	/**
	 * Initializes String buffer element
	 *
	 * @param string $filename
	 */
	function __construct($filename){
		$this->_Filename = $filename;
	}

	/**
	 * Get length of the item.
	 *
	 * As this item is a file it returns size of the file
	 *
	 * @return integer
	 */
	function getLength(){
		if(isset($this->_String)){ return parent::getLength(); }
		$size = filesize($this->_Filename);
		if($size === false){
			throw new Exception("StringBufferFileItem: cannot get the size of file $this->_Filename");
		}
		return $size;
	}

	function flush(){
		if(isset($this->_String)){ return parent::flush(); }
		readfile($this->_Filename);
	}

	/**
	 * Outputs content of buffer as string.
	 *
	 * @return string
	 */
	function toString(){
		if(isset($this->_String)){ return parent::toString(); }
		$content = Files::GetFileContent($this->_Filename,$err,$err_msg);
		if($err){
			throw new Exception("StringBufferFileItem: cannot read file $this->_Filename ($err_msg)");
		}
		return $content;
	}

	/**
	 * Replaces part of a string with another string.
	 *
	 * @param string $search
	 * @param string $replace
	 */
	function replace($search,$replace){
		$this->_String = $this->toString();
		return parent::replace($search,$replace);
	}

	function substr($offset,$length = null){
		if(is_null($length)){
			$length = $this->getLength() - $offset;
		}
		$f = fopen($this->_Filename,"rb"); // reading + binary
		if($f === false){
			throw new Exception("cannot open file $this->_Filename for reading");
		}
		$ret = fseek($f,$offset);
		if($ret !== 0){
			throw new Exception("cannot do fseek in file $this->_Filename");
		}
		$out = fread($f,$length);
		if($out === false){
			throw new Exception("cannot read from file $this->_Filename");
		}
		fclose($f);
		return $out;
	}
}

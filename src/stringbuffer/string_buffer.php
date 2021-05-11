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
	protected $_Items = array();
	
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
		return join("",$this->_Items);
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

	function getItems(){
		return $this->_Items;
	}

	function getLastItem(){
		return $this->_Items ? $this->_Items[sizeof($this->_Items)-1] : null;
	}
	
	/**
	 * Adds another string to the buffer.
	 *
	 * @param string $string_to_add
	 */
	function addString($string_to_add){
		settype($string_to_add,"string");
		if(strlen($string_to_add)>0){
			$this->_Items[] = new StringBufferItem($string_to_add);
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
		$this->_Items[] = new StringBufferFileItem($filename);
	}

	/**
	 * Adds content of another StringBuffer to the buffer.
	 *
	 * @param StringBuffer $stringbuffer_to_add
	 */
	function addStringBuffer($stringbuffer_to_add){
		if(!isset($stringbuffer_to_add)){ return;}
		for($i=0;$i<sizeof($stringbuffer_to_add->_Items);$i++){
			$this->_Items[] = $stringbuffer_to_add->_Items[$i];
		}
	}

	/**
	 * Returns length of buffer content.
	 *
	 * @return integer
	 */
	function getLength(){
		$out = 0;
		for($i=0;$i<sizeof($this->_Items);$i++){
			$out = $out + $this->_Items[$i]->getLength();
		}
		return $out;
	}

	/**
	 * Echoes content of buffer.
	 */
	function printOut(){
		for($i=0;$i<sizeof($this->_Items);$i++){
			$this->_Items[$i]->flush();
		}
	}

	/**
	 * Clears buffer.
	 */
	function clear(){
		$this->_Items = array();
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

		for($i=0;$i<sizeof($this->_Items);$i++){
			$this->_Items[$i]->replace($search,$replace);
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
		foreach($this->_Items as $b){
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

	/**
	 * Writes the whole content to the given file
	 *
	 * An exception is thrown when something went wrong with the file.
	 *
	 * 	$buffer->writeToFile("path/to/file.dat");
	 *
	 * @param string $filename
	 */
	function writeToFile($filename){
		if(!file_exists($filename)){
			// File is created with class Files in order to maintain file permissions
			Files::TouchFile($filename,$err,$err_str);
			if($err){
				throw new Exception(get_class($this).": cannot do touch on $filename ($err_msg)");
			}
		}

		$total_length = $this->getLength();
		$chunk_size = 1024 * 1024; // 1MB
		$bytes_written = 0;

		if($total_length===0){
			Files::EmptyFile($filename,$err,$err_str);
			if($err){
				throw new Exception(get_class($this).": cannot empty file $filename ($err_msg)");
			}
			return;
		}

		$f = fopen($filename,"w");
		if($f === false){
			throw new Exception(get_class($this).": cannot open $filename for writing");
		}
		while($bytes_written < $total_length){
			$length = min($chunk_size,$total_length - $bytes_written);
			$chunk = $this->substr($bytes_written,$length);
			$_bytes = fwrite($f,$chunk,$length);
			if($_bytes !== $length){
				throw new Exception(get_class($this).": cannot write to $filename");
			}
			$bytes_written += $length;
		}
		fclose($f);
	}
}

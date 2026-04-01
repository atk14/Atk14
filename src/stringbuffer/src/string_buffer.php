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
		$string_to_add = (string)$string_to_add;
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

	/**
	 * Returns all items stored in the buffer.
	 *
	 * @return StringBufferItem[]
	 */
	function getItems(){
		return $this->_Items;
	}

	/**
	 * Returns the last item in the buffer, or null if the buffer is empty.
	 *
	 * @return StringBufferItem|null
	 */
	function getLastItem(){
		return $this->_Items ? $this->_Items[sizeof($this->_Items)-1] : null;
	}
	
	/**
	 * Adds another string to the buffer.
	 *
	 * @param string $string_to_add
	 */
	function addString($string_to_add){
		$string_to_add = (string)$string_to_add;
		if(strlen($string_to_add)>0){
			$this->_Items[] = new StringBufferItem($string_to_add);
		}
	}

	/**
	 * Alias for addString()
	 */
	function add($string_to_add){
		return $this->addString($string_to_add);
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
		if(is_null($stringbuffer_to_add)){ return;}
		foreach($stringbuffer_to_add->getItems() as $item){
			$this->_Items[] = $item;
		}
	}

	/**
	 * Returns length of buffer content.
	 *
	 * @return integer
	 */
	function getLength(){
		$out = 0;
		foreach($this->getItems() as $item){
			$out += $item->getLength();
		}
		return $out;
	}

	/**
	 * Echoes content of buffer.
	 */
	function printOut(){
		foreach($this->getItems() as $item){
			$item->flush();
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
		$search = (string)$search;
		$replace = (string)$replace;

		foreach($this->_Items as &$item){	
			$item->replace($search,$replace);
		}
	}

	/**
	 * Returns the portion of buffered string specified by the offset and length parameters
	 *
	 *	$same = $buffer->substr(0);
	 *	$one_less_byte = $buffer->substr(1);
	 *	$last_5_bytes = $buffer->substr(-5);
	 *	$part = $buffer->substr(10,20);
	 */
	function substr($offset,$length = null){
		if($offset<0){
			if(is_null($length)){
				$offset = $this->getLength() - abs($offset);
			}
			if($offset<0){
				$length = is_null($length) ? $length : $length - abs($offset);
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
				throw new Exception(get_class($this).": cannot do touch on $filename ($err_str)");
			}
		}

		$total_length = $this->getLength();
		$chunk_size = 1024 * 1024; // 1MB
		$bytes_written = 0;

		if($total_length===0){
			Files::EmptyFile($filename,$err,$err_str);
			if($err){
				throw new Exception(get_class($this).": cannot empty file $filename ($err_str)");
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

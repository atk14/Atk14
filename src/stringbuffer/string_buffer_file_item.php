<?php
/**
 * Element to be added to StringBuffer as file
 *
 * @package Atk14\StringBuffer
 */
class StringBufferFileItem extends StringBufferItem{

	protected $_Filename;

	/**
	 * Initializes String buffer element
	 *
	 * @param string $filename
	 */
	function __construct($filename){
		$this->_Filename = $filename;
	}

	function getFilename(){
		return $this->_Filename;
	}

	function getContent(){
		if(!is_null($this->_String)){ return parent::getContent(); }
		$content = Files::GetFileContent($this->_Filename,$err,$err_msg);
		if($err){
			throw new Exception(get_class($this).": cannot read file $this->_Filename ($err_msg)");
		}
		return $content;
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
			throw new Exception(get_class($this).": cannot get the size of file $this->_Filename");
		}
		return $size;
	}

	function flush(){
		if(isset($this->_String)){ return parent::flush(); }
		readfile($this->_Filename);
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
		if(!is_null($this->_String)){
			return parent::substr($offset,$length);
		}

		if(is_null($length)){
			$length = $this->getLength() - $offset;
		}
		$f = fopen($this->_Filename,"rb"); // reading + binary
		if($f === false){
			throw new Exception(get_class($this).": cannot open file $this->_Filename for reading");
		}
		$ret = fseek($f,$offset);
		if($ret !== 0){
			throw new Exception(get_class($this).": cannot do fseek in file $this->_Filename");
		}
		$out = fread($f,$length);
		if($out === false){
			throw new Exception(get_class($this).": cannot read from file $this->_Filename");
		}
		fclose($f);
		return $out;
	}
}

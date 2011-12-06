<?php
/**
 * Class provides operations on files uploaded via asynchronous requests
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Class provides operations on files uploaded via asynchronous requests
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @author Jaromir Tomek
 */
class HTTPXFile extends HTTPUploadedFile{
	/**
	 * @param array $options
	 * @return HTTPXFile
	 */
	static function GetInstance($options = array()){
		global $HTTP_REQUEST;

		$options = array_merge(array(
			"name" => "file",
		),$options);

		if($HTTP_REQUEST->post() && ($filename = $HTTP_REQUEST->getHeader("X-File-Name"))){
			$out = new HTTPXFile();
			$out->_writeTmpFile($HTTP_REQUEST->getRawPostData());
			$out->_FileName = $filename;
			$out->_Name = $options["name"];
			return $out;
		}
	}

	function chunkedUpload(){
		return !is_null($this->_getChunkOrder());
	}

	/**
	 * Returns the current chunk order.
	 * Ordering starts from 1.
	 */
	function chunkOrder(){
		if($ar = $this->_getChunkOrder()){
			return $ar[0];
		}
	}

	/**
	 * Returns total amount of chunks.
	 */
	function chunksTotal(){
		if($ar = $this->_getChunkOrder()){
			return $ar[1];
		}
	}

	/**
	 * Returns array(1,5) on the first chunk
	 * and array(5,5) on the last chunk.
	 *
	 */
	function _getChunkOrder(){
		global $HTTP_REQUEST;
		$ch = $HTTP_REQUEST->getHeader("X-File-Chunk");
		if(preg_match('/^(\d+)\/(\d+)$/',$ch,$matches)){
			$order = $matches[1]+0;
			$total = $matches[2]+0;
			return array($order,$total);
		}
	}

	/**
	 * Is this the first chunk?
	 */
	function firstChunk(){ return $this->chunkOrder()==1; }

	/**
	 * Is this the last chunk?
	 */
	function lastChunk(){ return $this->chunkOrder()>0 && $this->chunkOrder()==$this->chunksTotal(); }

	/**
	 * An unique string for every chunked upload.
	 * All chunks in the same upload has the same token.
	 * 
	 * May be useful for proper chunked upload handling.
	 */
	function getToken(){
		global $HTTP_REQUEST;
		return $HTTP_REQUEST->getHeader("X-File-Token");
	}

	/**
	 * @access private
	 */
	function _writeTmpFile($content){
		if($this->_TmpFileName){ return; }
		$this->_TmpFileName = TEMP."/http_x_file_".uniqid().rand(0,9999);
		files::write_to_file($this->_TmpFileName,$content,$err,$err_str);
	}
}

<?php
/**
 * Class provides operations on files uploaded via asynchronous requests
 *
 * @filesource
 */

/**
 * Class provides operations on files uploaded via asynchronous requests
 *
 * It is not actually needed to use initialization.
 * It is used and provided by FileInput widget and thus you can retrieve file information from a FileField after a form validation.
 *
 * @package Atk14\Http
 */
class HTTPXFile extends HTTPUploadedFile{

	function __construct(){
		global $HTTP_REQUEST;

		parent::__construct();

		if(preg_match('/\bfilename="(.*?)"/',$HTTP_REQUEST->getHeader("Content-Disposition"),$matches)){ // Content-Disposition:	attachment; filename="DSC_0078.JPG"
			$this->_FileName = $matches[1];

		// legacy way
		}elseif($filename = $HTTP_REQUEST->getHeader("X-File-Name")){
			$this->_FileName = $filename;

		}
	}

	/**
	 * Initialize instance with uploaded file
	 *
	 * @param array $options
	 * @return HTTPXFile
	 */
	static function GetInstance($options = array()){
		global $HTTP_REQUEST;

		$options = array_merge(array(
			"name" => "file",
			"request" => null,
		),$options);

		if($HTTP_REQUEST->post() && (preg_match('/^attachment/',$HTTP_REQUEST->getHeader("Content-Disposition")) || $HTTP_REQUEST->getHeader("X-File-Name"))){
			$out = new HTTPXFile();
			$out->_writeTmpFile($HTTP_REQUEST->getRawPostData());
			$out->_Name = $options["name"];
			return $out;
		}
	}

	/**
	 * Is this a chunked file upload?
	 * 
	 * @return boolean
	 */
	function chunkedUpload(){
		return !is_null($this->_getChunkOrder()) && !($this->firstChunk() && $this->lastChunk());
	}

	/**
	 * Returns the current chunk order.
	 * Ordering starts from 1.
	 *
	 * @return integer
	 */
	function chunkOrder(){
		if($ar = $this->_getChunkOrder()){
			return $ar[0];
		}
	}

	/**
	 * Returns total amount of chunks.
	 *
	 * @return integer
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

		$content_range = $HTTP_REQUEST->getHeader("Content-Range"); // Content-Range: bytes 0-1048575/2344594
		if(preg_match('/^bytes (\d+)-(\d+)\/(\d+)$/',$content_range,$matches)){
			$start_offset = $matches[1];
			$end_offset = $matches[2];
			$total_length = $matches[3];
			if(!($total_length>0 && $end_offset>$start_offset && $end_offset<$total_length)){ // sanitization never hurts
				return null;
			}

			if($start_offset==0 && $end_offset+1==$total_length){
				return array(1,1);
			}

			if($start_offset==0){
				return array(1,3);
			}

			if($start_offset>0 && $end_offset+1<$total_length){
				return array(2,3);
			}

			if($start_offset>0 && $end_offset+1==$total_length){
				return array(3,3);
			}

			assert(false);
		}

		// legacy way
		$ch = $HTTP_REQUEST->getHeader("X-File-Chunk");
		if(preg_match('/^(\d+)\/(\d+)$/',$ch,$matches)){
			$order = $matches[1]+0;
			$total = $matches[2]+0;
			return array($order,$total);
		}
	}

	/**
	 * Is this the first chunk?
	 *
	 * @return boolean
	 */
	function firstChunk(){ return $this->chunkOrder()==1; }

	/**
	 * Is this the last chunk?
	 *
	 * @return boolean
	 */
	function lastChunk(){ return $this->chunkOrder()>0 && $this->chunkOrder()==$this->chunksTotal(); }

	/**
	 * An unique string for every chunked upload.
	 * All chunks in the same upload has the same token.
	 * 
	 * May be useful for proper chunked upload handling.
	 *
	 * @return string
	 */
	function getToken(){
		global $HTTP_REQUEST;
		if($HTTP_REQUEST->getHeader("X-File-Token")){ // legy way
			return substr(preg_replace('/[^a-z0-9_-]/i','',$HTTP_REQUEST->getHeader("X-File-Token")),0,20); // little sanitization never harms
		}

		return md5($HTTP_REQUEST->getHeader("Content-Disposition"));
	}

	/**
	 * Write file to temporary place
	 *
	 * @param string $content data to write
	 * @ignore
	 */
	private function _writeTmpFile($content){
		if($this->_TmpFileName){ return; }
		$this->_TmpFileName = TEMP."/http_x_file_".uniqid().rand(0,9999);
		Files::WriteToFile($this->_TmpFileName,$content,$err,$err_str);
	}
}

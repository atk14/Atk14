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

	/**
	 * Request used to process uploaded files
	 *
	 * @var HTTPRequest
	 */
	var $_Request;

	/**
	 * Constructor
	 *
	 * @param array $options
	 * - request HTTPRequest
	 */
	function __construct($options = array()){
		global $HTTP_REQUEST;

		$options += array(
			"request" => $HTTP_REQUEST
		);

		parent::__construct();

		$this->_Request = $options["request"];

		if(preg_match('/\bfilename="(.*?)"/',$this->_Request->getHeader("Content-Disposition"),$matches)){ // Content-Disposition:	attachment; filename="DSC_0078.JPG"
			$filename = $matches[1];
			$filename = urldecode($filename);
			$this->_FileName = $filename;

		// legacy way
		}elseif($filename = $this->_Request->getHeader("X-File-Name")){
			$this->_FileName = $filename;

		}
	}

	/**
	 * Destructor
	 */
	function __destruct(){
		$this->_autoCleanUp();
	}

	/**
	 * Initialize instance with uploaded file
	 *
	 * ```
	 * $f = HTTPXFile::GetInstance();
	 * $f = HTTPXFile::GetInstance("image");
	 * ```
	 *
	 * @param array $options
	 * @param string $name
	 * @param array $_ignore ignore it. it's present just for PHP7 compatibility because parent class has the same method with three params
	 * @return HTTPXFile
	 */
	static function GetInstance($options = array(),$name = "file",$_ignore = array()){
		global $HTTP_REQUEST;

		if(is_string($options)){
			$name = $options;
			$options = array();
		}

		$options += array(
			"name" => $name,
			"request" => $HTTP_REQUEST,
		);

		$request = $options["request"];

		if($request->post() && (preg_match('/^attachment/',$request->getHeader("Content-Disposition")) || $request->getHeader("X-File-Name"))){
			$content = $request->getRawPostData();

			$content_length = $request->getHeader("Content-Length");
			if(!is_null($content_length)){
				$content_length = (int)$content_length;
				if($content_length<0 || strlen($content)!=$content_length){
					return;
				}
			}else{
				if(strlen($content)==0){
					// An empty file without "Content-Length" header? It doesn't look good.
					return;
				}
			}

			$out = new HTTPXFile(array("request" => $request));
			$out->_writeTmpFile($content);
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
		if($crd = $this->_getContentRangeData()){
			$start_offset = $crd["start_offset"];
			$end_offset = $crd["end_offset"];
			$total_size = $crd["total_size"];

			if($start_offset==0 && $end_offset+1==$total_size){
				return array(1,1);
			}

			if($start_offset==0){
				return array(1,3);
			}

			if($start_offset>0 && $end_offset+1<$total_size){
				return array(2,3);
			}

			if($start_offset>0 && $end_offset+1==$total_size){
				return array(3,3);
			}

			throw new Exception("HTTPXFile: Content range values out of expectations");
		}

		// legacy way
		$ch = $this->_Request->getHeader("X-File-Chunk");
		if(preg_match('/^(\d+)\/(\d+)$/',$ch,$matches)){
			$order = $matches[1]+0;
			$total = $matches[2]+0;
			return array($order,$total);
		}
	}

	/**
	 * Decodes Content-Range http header if present
	 *
	 * @return array
	 */
	protected function _getContentRangeData(){
		$content_range = $this->_Request->getHeader("Content-Range"); // Content-Range: bytes 0-1048575/2344594
		if(preg_match('/^bytes (\d+)-(\d+)\/(\d+)$/',$content_range,$matches)){
			$start_offset = $matches[1];
			$end_offset = $matches[2];
			$total_size = $matches[3];
			if(!($total_size>0 && $end_offset>$start_offset && $end_offset<$total_size)){ // sanitization never hurts
				return null;
			}
			return array(
				"start_offset" => $start_offset,
				"end_offset" => $end_offset,
				"total_size" => $total_size,
			);
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
	 * Returned string can be safely used as a part of a temporary file's name.
	 *
	 * @param array $options
	 * - consider_remote_addr
	 * @return string
	 */
	function getToken($options = array()){
		$options += array(
			"consider_remote_addr" => true,
		);

		if($this->_Request->getHeader("X-File-Token")){ // legacy way
			return substr(preg_replace('/[^a-z0-9_-]/i','',$this->_Request->getHeader("X-File-Token")),0,20); // little sanitization never harms
		}

		if(!$crd = $this->_getContentRangeData()){
			return null;
		}

		$token_data = $this->getFileName().$crd["total_size"];
		if($options["consider_remote_addr"]){ $token_data .= $this->_Request->getRemoteAddr(); }

		return md5($token_data);
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

	/**
	 * Method for cleanup
	 */
	private function _autoCleanUp(){
		if($this->_TestingMode){ return; } // no auto file deletion in the testing mode
		if(!$this->_FileMoved && ($tmp_file = $this->getTmpFileName()) && file_exists($tmp_file)){
			Files::Unlink($tmp_file,$err,$err_str);
		}
	}
}

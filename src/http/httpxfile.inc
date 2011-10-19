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

	/**
	 * @access private
	 */
	function _writeTmpFile($content){
		if($this->_TmpFileName){ return; }
		$this->_TmpFileName = TEMP."/http_x_file_".uniqid().rand(0,9999);
		files::write_to_file($this->_TmpFileName,$content,$err,$err_str);
	}
}

<?
class HTTPXFile extends HTTPUploadedFile{
	function GetInstance($options = array()){
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

	function _writeTmpFile($content){
		if($this->_TmpFileName){ return; }
		$this->_TmpFileName = TEMP."/http_x_file_".uniqid().rand(0,9999);
		files::write_to_file($this->_TmpFileName,$content,$err,$err_str);
	}
}

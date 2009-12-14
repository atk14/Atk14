<?
class HTTPUploadedFile{
	var $_FILE = array();
	var $_Name = "";
	var $_TmpFileName = "";
	var $_MimeType = "";

	function GetInstances($options = array()){
		global $_FILES;

		$out = array();
		
		if(!isset($_FILES)){ return $out; }

		reset($_FILES);
		while(list($name,$FILE) = each($_FILES)){
			if($obj = HTTPUploadedFile::GetInstance($FILE,$name,$options)){
				$out[] = $obj;
			}
		}

		return $out;
	}

	/**
	* $file = HTTPUploadedFile::GetInstance($_FILE["userfile"],"userfile");
	*/
	function GetInstance($FILE,$name,$options = array()){
		$options = array_merge(array(
			"testing_mode" => false
		),$options);
		if(!$options["testing_mode"] && !is_uploaded_file($FILE["tmp_name"])){
			return null;
		}
		$out = new HTTPUploadedFile();
		$out->_FILE = $FILE;
		$out->_TmpFileName = $FILE["tmp_name"];
		$out->_MimeType = $out->_determineFileType($FILE);
		$out->_Name = $name;
		return $out;
	}

	function getName(){
		return $this->_Name;
	}
	
	function getFileName(){
		$filename = $this->_FILE["name"];
		if($filename==""){ $filename = "_"; }
		$filename = preg_replace("/[^a-zA-Z0-9_. -]/","_",$filename);
		return $filename;
	}

	function getFileSize(){
		return filesize($this->getTmpFileName());
	}

	function getMimeType(){
		//return $this->_FILE["type"];
		return $this->_MimeType;
	}

	/**
	* 
	*/
	function moveTo($new_filename){
		if(is_dir($new_filename)){
			$new_filename = "$new_filename/".$this->getFileName();
		}
		if(files::move_file($this->getTmpFileName(),$new_filename,$error,$error_str)==1){
			$this->_TmpFileName = $new_filename;
			return true;
		}
		return false;
	}

	function getContent(){
		return files::get_file_content($this->getTmpFileName(),$error,$error_str);
	}

	function getTmpFileName(){
		return $this->_TmpFileName;
	}

	function isImage(){ return preg_match("/^image\\/.+/",$this->getMimeType())>0; }
	function isPdf(){ return $this->getMimeType()=="application/pdf"; }

	function getImageWidth(){
		$this->_determineImageGeometry();
		return $this->_ImageWidth;
	}

	function getImageHeight(){
		$this->_determineImageGeometry();
		return $this->_ImageHeight;	
	}

	function _determineFileType($FILE){
		$command = "file -i ".escapeshellarg($FILE["tmp_name"]);
		$out = `$command`;
		// /tmp/xxsEEws: text/plain charset=us-ascii
		// -> text/plain
		if(preg_match("/^.*?:\\s*([^\\s]+\\/[^\\s]+)/",$out,$matches)){
			$mime_type = $matches[1];
		}else{
			$mime_type = "application/octet-stream";
		}
		return $mime_type;
	}

	function _determineImageGeometry(){
		if(isset($this->_ImageWidth)){ return; }

		$this->_ImageWidth = null;
		$this->_ImageHeight = null;

		if(!$this->isImage()){ return; }
		$ar = getimagesize($this->getTmpFileName());

		list($this->_ImageWidth,$this->_ImageHeight) = $ar;
	}
}
?>

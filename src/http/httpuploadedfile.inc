<?php
/**
 * Class provides operations on uploaded file.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @author Jaromir Tomek
 * @filesource
 */

/**
 * Class provides operations on uploaded file.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @author Jaromir Tomek
 *
 */
class HTTPUploadedFile{

	/**
	 * @var array
	 * @access private
	 */
	var $_FILE = array();

	/**
	 * @var string
	 * @access private
	 */
	var $_Name = ""; // image

	/**
	 * @var string
	 * @access private
	 */
	var $_TmpFileName = ""; // /tmp/Xis403s

	/**
	 * The original name of the file on the client machine.
	 *
	 * @var string
	 * @access private
	 */
	var $_FileName  = ""; // my_image.jpg

	/**
	 * @var string
	 * @access private
	 */
	var $_MimeType = null;

	static function GetInstances($options = array()){
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
	 * Returns instance of file object.
	 *
	 * <code>
	 * $file = HTTPUploadedFile::GetInstance($_FILE["userfile"],"userfile");
	 * </code>
	 * 
	 * @param $FILE
	 * @param string $name
	 * @param array $options
	 *
	 * @return HTTPUploadedFile
	 * @static
	 */
	static function GetInstance($FILE,$name,$options = array()){
		$options = array_merge(array(
			"testing_mode" => false
		),$options);
		if(!$options["testing_mode"] && !is_uploaded_file($FILE["tmp_name"])){
			return null;
		}
		$out = new HTTPUploadedFile();
		$out->_FILE = $FILE;
		$out->_TmpFileName = $FILE["tmp_name"];
		$out->_Name = $name;
		$out->_FileName = $FILE["name"];
		return $out;
	}

	/**
	 * Returns name of file.
	 *
	 * It's the name specified in the file field.
	 *
	 * @return string
	 */
	function getName(){
		return $this->_Name;
	}
	
	/**
	 * Returns original name of the file on a client machine.
	 *
	 * @return string
	 */
	function getFileName(){
		$filename = $this->_FileName;
		if($filename==""){ $filename = "_"; }
		$filename = preg_replace("/[^a-zA-Z0-9_. -]/","_",$filename);
		return $filename;
	}

	/**
	 * Gets file size.
	 *
	 * Returns the size of the file in bytes, or FALSE (and generates an error of level E_WARNING) in case of an error.
	 * @uses filesize
	 * @return int
	 */
	function getFileSize(){
		return filesize($this->getTmpFileName());
	}

	/**
	 * Returns MIME type.
	 *
	 * Tries to determine MIME type using system command 'file'.
	 * When MIME type is not recognized, method returns 'application/octet-stream'
	 *
	 * @return string string with MIME type
	 */
	function getMimeType(){
		if(!$this->_MimeType){ return $this->_determineFileType(); }
		return $this->_MimeType;
	}

	/**
	 * Moves the file from temporary directory to a new place.
	 *
	 * @param string $new_filename
	 * @return bool true, false when an error occurs
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

	/**
	 * Moves the file to applications temporary directory.
	 *
	 * You can specify custom filename, or method generates unique filename.
	 *
	 * <code>
	 * $file->moveToTemp();
	 * $file->moveToTemp("my_image.jpg");
	 * </code>
	 *
	 * @param string $filename custom filename
	 * @uses @moveToTemp()
	 * @return bool true, false when error occurs
	 */
	function moveToTemp($filename = ""){
		if(!$filename){ $filename = "moved_uploaded_file_".uniqid().rand(1,9999 ); }
		return $this->moveTo(TEMP."/$filename");
	}

	/**
	 * Removes temporary file when some exists.
	 *
	 */
	function cleanUp(){
		if($tmp_file = $this->getTmpFileName()){
			files::unlink($tmp_file,$err,$err_str);
		}
	}

	/**
	 * Gets content of a file.
	 *
	 * @return mixed
	 */
	function getContent(){
		return files::get_file_content($this->getTmpFileName(),$error,$error_str);
	}

	/**
	 * Return name of temporary file.
	 *
	 * @return string
	 */
	function getTmpFileName(){
		return $this->_TmpFileName;
	}

	/**
	 * Checks if the file is an image.
	 *
	 * @return bool
	 */
	function isImage(){ return preg_match("/^image\\/.+/",$this->getMimeType())>0; }

	/**
	 * Checks if the file is a PDF file.
	 *
	 * @return bool
	 */
	function isPdf(){ return $this->getMimeType()=="application/pdf"; }

	/**
	 * Gets image width if the file is an image.
	 *
	 * @return int
	 */
	function getImageWidth(){
		$this->_determineImageGeometry();
		return $this->_ImageWidth;
	}

	/**
	 * Gets image height if the file is an image.
	 *
	 * @return int
	 */
	function getImageHeight(){
		$this->_determineImageGeometry();
		return $this->_ImageHeight;	
	}

	/**
	 * @access private
	 * @return string
	 */
	function _determineFileType(){
		$command = "file -i ".escapeshellarg($this->getTmpFileName());
		$out = `$command`;
		// /tmp/xxsEEws: text/plain charset=us-ascii
		// -> text/plain
		// ya.gif: image/gif; charset=binary
		// -> image/gif
		if(preg_match("/^.*?:\\s*([^\\s]+\\/[^\\s;]+)/",$out,$matches)){
			$mime_type = $matches[1];
		}else{
			$mime_type = "application/octet-stream";
		}
		return $mime_type;
	}

	/**
	 * @access private
	 */
	function _determineImageGeometry(){
		if(isset($this->_ImageWidth)){ return; }

		$this->_ImageWidth = null;
		$this->_ImageHeight = null;

		if(!$this->isImage()){ return; }
		$ar = getimagesize($this->getTmpFileName());

		list($this->_ImageWidth,$this->_ImageHeight) = $ar;
	}
}

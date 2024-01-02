<?php
/**
 * Provides access to uploaded file.
 *
 * Uploaded file is accessible as {@link HTTPUploadedFile}
 *
 * <code>
 *	$f = new FileField(array(
 *		"max_file_size" => "5M", // 1024 * 1024, "1M", "2MB", "10kB", "20KB"
 *		"allowed_mime_types" => array("application/pdf","/^image\//"), // regular expressions are possible
 *	));
 * </code>
 *
 * @package Atk14
 * @subpackage Forms
 */
class FileField extends Field{

	var $max_file_size;
	var $allowed_mime_types;

	function __construct($options = array()){
		$options += array(
			"max_file_size" => null, // 1024, "1.5MB",
			"allowed_mime_types" => array(), // array("application/pdf","/^image\/.*/")
			"widget" => new FileInput(),
		);

		$this->max_file_size = $this->_fileSize2Int($options["max_file_size"]);
		unset($options["max_file_size"]);

		$this->allowed_mime_types = $options["allowed_mime_types"];
		unset($options["allowed_mime_types"]);

		if($this->max_file_size){
			$options["widget"]->attrs["data-max-file-size"] = $this->max_file_size;
		}

		parent::__construct($options);

		$this->update_messages(array(
			"file_too_big" => _('Ensure this file has at most %max_file_size% bytes (it has %file_size%)'),
			"disallowed_mime_type" => _('Unsupported file type: %mime_type%'),

			"ini_size" => _('The uploaded file exceeds the max file size value that is set on the server (%upload_max_filesize%)'),
			"form_size" => _('The uploaded file exceeds the max file size directive that was specified in the HTML form (%MAX_FILE_SIZE%)'),
			"partial" => _('The uploaded file was only partially uploaded'),
			"no_tmp_dir" => _('Missing a temporary folder'),
			"cant_write" => _('Failed to write file to disk'),
			"extension" => _('File upload stopped by an extension installed on the server'),
			"unknown_error" => _('An error occurred during file upload'),
		));
	}

	function clean($value){
		if(is_numeric($value)){ // file upload error code! http://php.net/manual/en/features.file-upload.errors.php
			foreach(array(
				"UPLOAD_ERR_INI_SIZE",
				"UPLOAD_ERR_FORM_SIZE",
				"UPLOAD_ERR_PARTIAL",
				"UPLOAD_ERR_NO_TMP_DIR",
				"UPLOAD_ERR_CANT_WRITE",
				"UPLOAD_ERR_EXTENSION",
			) as $err_code_name){
				if($value==constant($err_code_name)){
					$_k = strtolower(preg_replace('/^UPLOAD_ERR_/','',$err_code_name)); // UPLOAD_ERR_INI_SIZE -> ini_size
					$_message = $this->messages[$_k];
					if($_k=="ini_size"){
						$_message = str_replace("%upload_max_filesize%",h(ini_get("upload_max_filesize")),$_message);
					}
					if($_k=="form_size"){
						$_message = str_replace("%MAX_FILE_SIZE%",h(isset($_POST["MAX_FILE_SIZE"]) ? $_POST["MAX_FILE_SIZE"] : ""),$_message);
					}
					return array($_message,null);
				}
			}
			return array($this->messages["unknown_error"],null);
		}

		list($err,$value) = parent::clean($value);
		if($err || !$value){ return array($err,$value); }
		if($this->max_file_size && $value->getFileSize()>$this->max_file_size){
			return array(	
				strtr($this->messages["file_too_big"],array("%max_file_size%" => $this->max_file_size, "%file_size%" => $value->getFileSize())),
				null
			);
		}
		if(!$this->_mimeTypeMatched($value->getMimeType(),$this->allowed_mime_types)){
			return array(
				strtr($this->messages["disallowed_mime_type"],array("%mime_type%" => h($value->getMimeType()))),
				null
			);
		}
		return array(null,$value);
	}

	/**
	 *
	 * $f->_fileSize2Int(null); // null
	 * $f->_fileSize2Int(""); // null
	 * $f->_fileSize2Int("1000"); // 1000
	 * $f->_fileSize2Int("1M"); // 1024
	 * $f->_fileSize2Int("1M"); // 1024
	 */
	function _fileSize2Int($size){
		$size = (string)$size;
		$size = preg_replace('/\s/','',$size);
		if(is_numeric($size)){ return (int)$size; }
		if(preg_match('/([0-9\.]+)(M|k|K)B?/',$size,$matches)){
			$multipliers = array(
				"k" => 1024,
				"K" => 1000,
				"M" => 1024 * 1024
			);
			return (int)($matches[1] * $multipliers[$matches[2]]);
		}
		return null; // Is there a need to throwing an exception?
	}

	/**
	 *
	 * $f->_mimeTypeMatched("image/jpeg",array("image/jpeg","image/png")); // true
	 * $f->_mimeTypeMatched("image/jpeg",array("/^image\//")); // true
	 */
	function _mimeTypeMatched($mime_type,$allowed_mime_types){
		if(!is_array($allowed_mime_types)){ $allowed_mime_types = array($allowed_mime_types); }
		if(sizeof($allowed_mime_types)==0){ return true; }
		$matched = false;
		foreach($allowed_mime_types as $amt){
			if(preg_match('/^\//',$amt)){
				if(preg_match($amt,$mime_type)){
					$matched = true; break;
				}
				continue;
			}
			if($amt==$mime_type){
				$matched = true; break;
			}
		}

		return $matched;
	}
}

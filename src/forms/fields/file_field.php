<?php
/**
 * Provides access to uploaded file.
 *
 * Uploaded file is accessible as {@link HTTPUploadedFile}
 *
 * @package Atk14
 * @subpackage Forms
 */
class FileField extends Field{
	function __construct($options = array()){
		$options += array(
			"max_file_size" => null, // 1024, "1.5MB",
			"widget" => new FileInput(),
		);

		$this->max_file_size = $this->_fileSize2Int($options["max_file_size"]);
		unset($options["max_file_size"]);

		if($this->max_file_size){
			$options["widget"]->attrs["data-max-file-size"] = $this->max_file_size;
		}

		parent::__construct($options);

		$this->update_messages(array(
			"file_too_big" => _('Ensure this file has at most %max_file_size% bytes (it has %file_size%)'),
		));
	}
	function clean($value){
		list($err,$value) = parent::clean($value);
		if($err || !$value){ return array($err,$value); }
		if($this->max_file_size && $value->getFileSize()>$this->max_file_size){
			return array(	
				strtr($this->messages["file_too_big"],array("%max_file_size%" => $this->max_file_size, "%file_size%" => $value->getFileSize())),
				null
			);
		}
		return array(null,$value);
	}

	/**
	 * $f->_fileSize2Int(null); // null
	 * $f->_fileSize2Int(""); // null
	 * $f->_fileSize2Int("1000"); // 1000
	 * $f->_fileSize2Int("1M"); // 1024
	 * $f->_fileSize2Int("1M"); // 1024
	 */
	function _fileSize2Int($size){
		$size = preg_replace('/\s/','',$size);
		if(is_numeric($size)){ return (int)$size; }
		if(preg_match('/([0-9\.]+)(M|k)B?/',$size,$matches)){
			$multipliers = array(
				"k" => 1024,
				"M" => 1024 * 1024
			);
			return (int)($matches[1] * $multipliers[$matches[2]]);
		}
		return null; // Is there a need to throwing an exception?
	}
}

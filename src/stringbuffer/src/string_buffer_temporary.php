<?php
/**
 * StringBufferTemporary writes every added content into a temporary file in order to minimize memory consumption
 *
 *	$buffer = new StringBufferTemporary();
 *
 * 	$buffer->add($megabyte);
 * 	$buffer->add($megabyte);
 * 	$buffer->add($megabyte);
 * 	$buffer->add($megabyte);
 *
 *	$buffer->writeToFile($target_filename);
 */
class StringBufferTemporary extends StringBuffer {

	static $FILEIZE_THRESHOLD = 1048576; // 1024 * 1024; 1MB

	function addString($string_to_add){
		$string_to_add = (string)$string_to_add;

		if(!strlen($string_to_add)){ return; }

		$last_item = $this->getLastItem();
		if($last_item && !$last_item->isFileized()){
			$last_item->addString($string_to_add);
		}else{
			$last_item = new StringBufferTemporaryItem($string_to_add);
			$this->_Items[] = $last_item;
		}

		if(!$last_item->isFileized() && ($last_item->getLength() >= static::$FILEIZE_THRESHOLD)){
			$last_item->fileize();
		}
	}
}

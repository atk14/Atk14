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

	function addString($string_to_add){
		settype($string_to_add,"string");

		$FILEIZE_THRESHOLD = defined("TEST") && constant("TEST") ? 5 : 1024 * 1024; // 5 bytes or 1MB

		$last_item = $this->getLastItem();
		if($last_item && !$last_item->isFileized()){
			$last_item->addString($string_to_add);
		}else{
			$last_item = new StringBufferTemporaryItem($string_to_add);
			$this->_Items[] = $last_item;
		}

		if(!$last_item->isFileized() && ($last_item->getLength() >= $FILEIZE_THRESHOLD)){
			$last_item->fileize();
		}
	}
}

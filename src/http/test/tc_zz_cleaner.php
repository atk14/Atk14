<?php
class tc_zz_cleaner extends tc_base{

	// removes working files from ./temp/
	function test(){
		$this->_init_FILES();

		foreach(HTTPUploadedFile::GetInstances(array("testing_mode" => true)) as $file){
			$file->cleanUp();
		}
	}
}

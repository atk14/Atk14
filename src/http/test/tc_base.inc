<?
class tc_base extends tc_super_base{

	function _init_FILES(){
		global $_FILES;

		files::copy_file("hlava.jpg",TEMP."/temp_hlava.jpg",$err,$err_str);
		files::copy_file("dousi.pdf",TEMP."/temp_dousi.pdf",$err,$err_str);

		$_FILES = array(
			"hlava" => array(
				"tmp_name" => TEMP."/temp_hlava.jpg",
				"name" => "Hlava.jpg"
			),
			"dousi" => array(
				"tmp_name" => TEMP."/temp_dousi.pdf",
				"name" => "Dousi.pdf"
			), 
		);
	}
}
?>

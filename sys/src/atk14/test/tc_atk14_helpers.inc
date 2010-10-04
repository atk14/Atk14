<?
class tc_atk14_helpers extends tc_base{
	function test_javascript_script_tag(){
		$out = $this->_run_action("helpers/javascript_script_tag");
		$mtime = filemtime("public/javascripts/site.js");
		//echo $out; exit;
		$this->assertEquals('<script src="/public/javascripts/site.js?'.$mtime.'" type="text/javascript"></script>
<script src="/public/javascripts/site.js?'.$mtime.'" type="text/javascript" media="screen"></script>
<!-- javascript file not found: '.PATH_ATK14_APPLICATION.'/../public/javascripts/nonexisting.js -->',$out);
	}

}
?>

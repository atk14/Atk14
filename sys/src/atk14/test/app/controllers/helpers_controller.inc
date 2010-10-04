<?
class HelpersController extends ApplicationController{
	function javascript_script_tag(){ }
	function _before_filter(){
		$this->render_layout = false;
	}
}
?>

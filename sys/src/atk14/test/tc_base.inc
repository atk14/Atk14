<?
class tc_base extends tc_super_base{
	function _run_action($path,$options = array()){
		list($controller,$action) = explode("/",$path);
		$response = Atk14Dispatcher::ExecuteAction($controller,$action);
		return $response->buffer->toString();
	}
}
?>

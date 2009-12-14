<?
if(!function_exists('json_encode'))
{
	require_once(dirname(__FILE__)."/json.inc");
	$GLOBALS['JSON_OBJECT'] = new Services_JSON();
	function json_encode($value)
	{
		_json_initialize();
		return $GLOBALS['JSON_OBJECT']->encode($value); 
	}
	
	function json_decode($value)
	{
		_json_initialize();
		return $GLOBALS['JSON_OBJECT']->decode($value); 
	}

	function _json_initialize(){
		if(!isset($GLOBALS['JSON_OBJECT'])){ $GLOBALS['JSON_OBJECT'] = new Services_JSON(); }
	}
}
?>

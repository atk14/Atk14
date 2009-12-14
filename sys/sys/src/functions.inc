<?
/**
* Vyhledava a nahrazuje ve stringu.
*
* echo EasyReplace("ahoj %koho%, jak se %co%?",array("%koho%" => "Valdo", "%co%" => "vejde"));
*
*	@param string		$str			vstupni string
*	@param array		$replaces	asociativni pole
*	@return	string						nahrazeny vstupni string
*/
function EasyReplace($str,$replaces){
	settype($str,"string");
  settype($replaces,"array");
  $_replaces_keys = array();
  $_replaces_values = array();
  reset($replaces);
  while(list($key,) = each($replaces)){
    $_replaces_keys[] = $key;
    $_replaces_values[] = $replaces[$key];
  }   
  if(sizeof($_replaces_keys)==0){
    return $str;
  }   
  return str_replace($_replaces_keys,$_replaces_values,$str);
}
?>

<?
/**
* Searches and replaces in a string.
*
*	<code>
* 	echo EasyReplace("Hi %who%, how it's %what%?",array("%who%" => "Valda", "%what%" => "going"));
* </code>
*
*	@param string		$str
*	@param array		$replaces	associative array
*	@return	strig
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

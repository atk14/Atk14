<?php
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

/**
* Alias for htmlspecialchars().
*/
function h($string, $flags = null, $encoding = null){
	if(!isset($flags)){
		$flags =  ENT_COMPAT;
		if(defined("ENT_HTML401")){ $flags = $flags | ENT_HTML401; }
	}
	if(!isset($encoding)){
 		// as of PHP5.4 the default encoding is UTF-8, it causes troubles in non UTF-8 applications,
		// I think that the encoding ISO-8859-1 works well in UTF-8 applications
		$encoding = "ISO-8859-1";
	}
	return htmlspecialchars($string,$flags,$encoding);
}

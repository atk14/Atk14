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
	settype($replaces,"array");
	return str_replace(array_keys($replaces),array_values($replaces),$str);
}

/**
* Alias for htmlspecialchars().
*/
function h($string, $flags = null, $encoding = null){
	if(!isset($flags)){
		$flags =  ENT_COMPAT;
		if(defined("ENT_HTML401")){ $flags = $flags | ENT_HTML401 | ENT_QUOTES; }
	}
	if(!isset($encoding)){
 		// as of PHP5.4 the default encoding is UTF-8, it causes troubles in non UTF-8 applications,
		// I think that the encoding ISO-8859-1 works well in UTF-8 applications
		$encoding = "ISO-8859-1";
	}
	return htmlspecialchars($string,$flags,$encoding);
}

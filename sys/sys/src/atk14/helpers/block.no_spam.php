<?
/**
* {no_spam}Napiste nam na adresu info@domena.cz.{/no_spam}
* 
* 
*/
function smarty_block_no_spam($params, $content, &$smarty, &$repeat){
	return __no_spam_filter__($content);
}

function __no_spam_filter__($content){
	return preg_replace("/([^\\s]+)@([^\\s]+)\\.([a-z]{2,5})/i","<span class=\"atk14_no_spam\">\\1[at-sign]\\2[dot-sign]\\3</span>",$content);
}
?>

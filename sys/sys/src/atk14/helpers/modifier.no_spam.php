<?
require_once(dirname(__FILE__)."/block.no_spam.php");
function smarty_modifier_no_spam($content){
	return __no_spam_filter__($content);
}

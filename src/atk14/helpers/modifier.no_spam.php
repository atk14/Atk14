<?php
require_once(dirname(__FILE__)."/block.no_spam.php");

/**
 *
 * ```
 * {"info@domena.cz"|no_spam}
 *
 * {"info@domena.cz"|no_spam:"class=btn btn-primary"}
 * {"info@domena.cz"|no_spam:"class=btn btn-primary,title=our email address"}
 * {"info@domena.cz"|no_spam:"text=email"}
 * ```
 */
function smarty_modifier_no_spam($content,$params = ""){
	$params = Atk14Utils::StringToOptions($params);
	return __no_spam_filter__($content,$params);
}

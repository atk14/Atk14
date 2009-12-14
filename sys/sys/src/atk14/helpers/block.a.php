<?
/**
* {a controller="domain" action="examination" lang="cs" domain_name="plovarna.cz"}Text odkazu{/a}
*
*
* tento zapis
* {a controller="domain" action="examination" lang="cs" domain_name="plovarna.cz" _anchor="detail"}Prohlizeni domeny plovarna.cz{/a}
* 
* vyprodukuje
* <a href="/prohlizeni-domeny/plovarna.cz/#detail">Prohlizeni domeny plovarna.cz</a>
*
*
* 
* tento zapis
* {a controller="domain" action="examination" lang="cs" domain_name="plovarna.cz" _with_hostname=true}Prohlizeni domeny plovarna.cz{/a}
* 
* vyprodukuje
* <a href="http://www.domainmaster.cz/prohlizeni-domeny/plovarna.cz/">Prohlizeni domeny plovarna.cz</a>
*
*
* 
* tento zapis
* {a controller="domain" action="examination" lang="cs" domain_name="plovarna.cz" _anchor="detail" _class="detail" _id="detail"}Prohlizeni domeny plovarna.cz{/a}
* 
* vyprodukuje
* <a href="/prohlizeni-domeny/plovarna.cz/#detail" class="detail" id="detail">Prohlizeni domeny plovarna.cz</a>
* 
* 
*/
function smarty_block_a($params, $content, &$smarty, &$repeat)
{
	Atk14Timer::Start("helper block.a");
	$attributes = array();

	$url = Atk14Utils::BuildLink($params,$smarty);

	$attrs = Atk14Utils::ExtractAttributes($params);
	$attrs["href"] = $url;
	$attrs = Atk14Utils::JoinAttributes($attrs);

	Atk14Timer::Stop("helper block.a");
	return "<a$attrs>$content</a>";
}
?>

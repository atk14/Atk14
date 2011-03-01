<?php
/**
 * Smarty {a}{/a} block tag.
 *
 * Smarty block tag {a}{/a} generates html <a /> tag
 *
 * <code>
 * {a controller="domain" action="examination" lang="cs" domain_name="plovarna.cz"}Text odkazu{/a}
 * </code>
 *
 * tento zapis
 * <code>
 * {a controller="domain" action="examination" lang="cs" domain_name="plovarna.cz" _anchor="detail"}Prohlizeni domeny plovarna.cz{/a}
 * </code>
 * 
 * vyprodukuje
 * <a href="/prohlizeni-domeny/plovarna.cz/#detail">Prohlizeni domeny plovarna.cz</a>
 *
 *
 * 
 * tento zapis
 * <code>
 * {a controller="domain" action="examination" lang="cs" domain_name="plovarna.cz" _with_hostname=true}Prohlizeni domeny plovarna.cz{/a}
 * </code>
 * 
 * vyprodukuje
 * <a href="http://www.domainmaster.cz/prohlizeni-domeny/plovarna.cz/">Prohlizeni domeny plovarna.cz</a>
 *
 *
 * 
 * tento zapis
 * <code>
 * {a controller="domain" action="examination" lang="cs" domain_name="plovarna.cz" _anchor="detail" _class="detail" _id="detail"}Prohlizeni domeny plovarna.cz{/a}
 * </code>
 * 
 * vyprodukuje
 * <a href="/prohlizeni-domeny/plovarna.cz/#detail" class="detail" id="detail">Prohlizeni domeny plovarna.cz</a>
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 *
 * @param array $params Parameters to build the <a /> tag
 * Reserved parameters are:
 * <ul>
 * 	<li><b>controller</b> - name of controller</li>
 * 	<li><b>action</b> - name of action in the specified controller</li>
 * 	<li><b>lang</b> - language. Defaults to ??</li>
 * 	<li><b>domain_name</b> - Generated url will contain this domain_name when used with _with_hostname=true</li>
 * 	<li><b>_with_hostname</b> - see domain_name parameter</li>
 * 	<li><b>_anchor</b> - generates anchor</li>
 * </ul>
 *
 * You can also define attributes of the tag. Simply add them to the helper with underscore at the beginning. For example parameter <b>_class=heading</b> will generate <a /> tag with attribute class="heading"
 * <code>
 * {a controller="domain" action="examination" _class="heading"}Prohlizeni domeny plovarna.cz{/a}
 * </code>
 *
 * More query parameters can be added by adding them to the helper. They will appear in the URL with the name you give them in helper.
 * @param string $content content of the Smarty {a} block tag
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

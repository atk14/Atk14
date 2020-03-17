<?php
/**
 * Smarty block plugin.
 *
 * Plugin simplifies ordering of listed records.
 * Wraps tagged content with a link containing parameters determining sorting. These parameters are recognized in a controller using {@link Atk14Sorting} class.
 *
 * In a template:
 * <code>
 * <tr>
 *		{sortable key=title}<th>Title</th>{/sortable}
 *		{sortable key=code}<th>Code</th>{/sortable}
 * <tr>
 * </code>
 *
 * Then in your controller:
 * <code>
 * $this->sorting->add("title",array("order_by" => "UPPER(title)"));
 * $this->sorting->add("code");
 *
 * $finder = Book::Finder(array(
 * 	"conditions" => $conditions,
 *  "bind_ar" => $bind_ar,
 *  "order" => $this->sorting->getOrder(),
 *  "limit" => 10,
 *  "offset" => $this->params->getInt("offset"),
 * ));
 * </code>
 *
 *
 * Another example:
 * <code>
 * <tr>
 *		{sortable key=name}<td>Name</td>{/sortable} 
 * </tr>
 * </code>
 *
 * or
 * <code>
 * <tr>
 *		{sortable key=name}Name{/sortable} 
 * </tr>
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 * Smarty block function.
 *
 * Reserved parameters:
 * <ul>
 * 	<li>key - name of the key to sort by corresponding to value passed to {@link Atk14Sorting::add()}</li>
 * </ul>
 *
 * @param array $params
 * @param string $content
 *
 */
function smarty_block_sortable($params, $content, $template, &$repeat){
	if($repeat){ return; }
	$smarty = atk14_get_smarty_from_template($template);

	$params = array_merge(array(
		// ??? TODO: neco jako wrap_with_th_tag => true
	),$params);
	$key = $params["key"];
	$sorting = $smarty->getTemplateVars("sorting");
	$_params = $smarty->getTemplateVars("params")->copy();
	$_params->delete(ATK14_PAGINATOR_OFFSET_PARAM_NAME); // smazani parametru pro strankovani
	$_key = "$key";
	if($sorting->getActiveKey()==$_key){
		$_key = "$key-desc";
	}
	$_params->s("order",$_key);
	$href = Atk14Url::BuildLink($_params->toArray(),array("connector" => "&amp;"));
	$_active = "";
	$_arrow = "";
	if(in_array($sorting->getActiveKey(),array("$key","$key-asc"))){
		$_active = " active";
		$_arrow = USING_FONTAWESOME ? ' <span class="fas fa-arrow-up"></span>' : ' <span class="arrow-up">&uArr;</span>';
	}elseif($sorting->getActiveKey()=="$key-desc"){
		$_active = " active";
		$_arrow = USING_FONTAWESOME ? ' <span class="fas fa-arrow-down"></span>' : ' <span class="arrow-down">&dArr;</span>';
	}
	$content = trim($content);
	// is there already an class attribute?
	$_class_orig = "";
	if(preg_match("/^<([^>]*)( class=\"([^\"]*)\")/",$content,$matches)){
		$_class_orig = $matches[3]." ";
		$content = preg_replace("/^<([^>]*)( class=\"([^\"]*)\")/","<\\1",$content);
	}
	$_class = " class=\"{$_class_orig}sortable$_active\"";
	if(preg_match("/^<([^>]+)>(.+)(<\\/[^>]+>)$/s",$content,$matches)){
		return "<$matches[1]$_class><a href=\"$href\" title=\"".h($sorting->getTitle($key))."\" rel=\"nofollow\">$matches[2]$_arrow</a>$matches[3]";
	}
	return "<a href=\"$href\" title=\"".h($sorting->getTitle($key))."\" rel=\"nofollow\"$_class>$content$_arrow</a>";
}

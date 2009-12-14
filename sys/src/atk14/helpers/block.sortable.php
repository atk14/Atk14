<?
/**
* Usage:
*
*		<tr>
*		{sortable key=name}<th>Name</th>{/sortable} 
*		</tr>
*
*		or
*
*		<tr>
*		{sortable key=name}<td>Name</td>{/sortable} 
*		</tr>
*
*		or
*
*		{sortable key=name}Name{/sortable} 
*/
function smarty_block_sortable($params, $content, &$smarty, &$repeat){
	$params = array_merge(array(
		// ??? TODO: neco jako wrap_with_th_tag => true
	),$params);
	$key = $params["key"];
	$sorting = $smarty->_tpl_vars["sorting"];
	$_params = $smarty->_tpl_vars["params"]->copy();
	$_params->delete("from"); // smazani parametru pro strankovani
	$_key = "$key-asc";
	if($sorting->getActiveKey()==$_key){
		$_key = "$key-desc";
	}
	$_params->s("order",$_key);
	$href = Atk14Url::BuildLink($_params->toArray());
	$_active = "";
	$_arrow = "";
	if($sorting->getActiveKey()=="$key-asc"){
		$_active = " active";
		$_arrow = " <span class=\"arrow-up\">&uArr;</span>";
	}elseif($sorting->getActiveKey()=="$key-desc"){
		$_active = " active";
		$_arrow = " <span class=\"arrow-down\">&dArr;</span>";
	}
	$_class = " class=\"sortable$_active\"";
	if(preg_match("/^<([^>]+)>(.+)(<\\/[^>]+>)$/",trim($content),$matches)){
		return "<$matches[1]$_class><a href=\"$href\" title=\"".htmlspecialchars($sorting->getTitle($key))."\" rel=\"nofolow\">$matches[2]$_arrow</a>$matches[3]";
	}
	return "<a href=\"$href\" title=\"".htmlspecialchars($sorting->getTitle($key))."\" rel=\"nofolow\"$_class>$content$_arrow</a>";
}
?>

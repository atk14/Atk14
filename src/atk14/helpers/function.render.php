<?php
/**
* {render partial="search_box"}
*	toto povede k natazeni sablonky _search_box.tpl
*
* {render partial="shared/user_info"}
* toto zase povede k natazeni sablonky shared/_user_info.tpl
*
*
* Render parial se pouziva i misto {foreach}
*
*	{render parial="product" from=$products item=product} 
*
* {render parial="article" from=$articles item=article key=article_id}
* 
* {render parial=article_item from=$articles item=article}
* {render parial=article_item from=$articles} {* zde bude item automaticky nastaveno na article *}
*
*
* Dale je mozne render pouzit misto for(;;){ }:
* {render parial="list_item" for=1 to=10 step=1 item=i}
*/
function smarty_function_render($params,$template){
	$smarty = atk14_get_smarty_from_template($template);

	Atk14Timer::Start("helper function.render");
	$template_name = $partial = $params["partial"];
	unset($params["partial"]);

	$template_name = preg_replace("/([^\\/]+)$/","_\\1",$template_name);
	$template_name .= ".tpl";

	if(in_array("from",array_keys($params)) && (!isset($params["from"]) || sizeof($params["from"])==0)){ return ""; }

	$original_tpl_vars = $smarty->getTemplateVars();

	$out = array();

	// in Smarty3 $smarty doesn't know variables assigned in a $template
	//
	//		{* template index.tpl *}
	//		{assign var="flower" value="rose"}
	//		{render prtial="partial"}
	//
	//		{* template _partial.tpl *}
	//		{$flower} {* normally $smarty doesn't know about a rose *}
	//
	// in order to avoid this behavior we have to assign all $template`s vars to $smarty
	$smarty->assign($template->getTemplateVars());

	if(!isset($params["from"])){
	
		foreach($params as $key => $value){	
			$smarty->assign($key,$value);
		}

		$out[] = $smarty->fetch($template_name);

	}else{

		$key = null;
		
		if(is_array($params["from"])){
			$collection = $params["from"];
			$key = isset($params["key"]) ? $params["key"] : null;
			unset($params["key"]);
		}else{
			$collection = array();
			$to = isset($params["to"]) ? (int)$params["to"] : 0;
			// TODO: poresit zaporny stepping
			$step = isset($params["step"]) ? (int)$params["step"] : 1;
			$step==0 && ($step = 1);
			
			for($i=(int)$params["from"];$i<=$to;$i += $step){
				$collection[] = $i;
			}
			unset($params["to"]);
			unset($params["step"]);
		}

		$item = null;
		if(isset($params["item"])){
			$item = $params["item"];
		}elseif(preg_match("/([^\\/]+)$/",$partial,$matches)){
			$item = $matches[1];
		}

		unset($params["item"]);
		unset($params["from"]);

		$collection_size = sizeof($collection);
		$counter = 0;
		foreach($collection as $_key => $_item){
			if(isset($key)){ $smarty->assign($key,$_key); }
			if(isset($item)){ $smarty->assign($item,$_item); }
			$smarty->assign("__counter__",$counter);
			$smarty->assign("__first__",$counter==0);
			$smarty->assign("__last__",$counter==($collection_size-1));

			// zbytek parametru se naasignuje do sablonky jako v predhozim pripade
			foreach($params as $key => $value){
				$smarty->assign($key,$value);
			}

			$out[] = $smarty->fetch($template_name);
			$counter++;
		}
	}

	// vraceni puvodnich hodnot do smarty objectu
	$smarty->clearAllAssign();
	$smarty->assign($original_tpl_vars);

	Atk14Timer::Stop("helper function.render");

	return join("",$out);
}

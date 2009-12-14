<?
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
*
* Dale je mozne render pouzit misto for(;;){ }:
* {render parial="list_item" for=1 to=10 step=1 item=i}
*/
function smarty_function_render($params,&$smarty){
	Atk14Timer::Start("helper function.render");
	$template_name = $params["partial"];
	unset($params["partial"]);

	$template_name = preg_replace("/([^\\/]+)$/","_\\1",$template_name);
	$template_name .= ".tpl";

	if(in_array("from",array_keys($params)) && (!isset($params["from"]) || sizeof($params["from"])==0)){ return ""; }

	$original_tpl_vars = $smarty->_tpl_vars;

	if(!isset($params["from"])){
	
		reset($params);
		while(list($key,$value) = each($params)){
			$smarty->assign($key,$value);
		}
		$smarty->display($template_name);

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

		$item = isset($params["item"]) ? $params["item"] : null;

		unset($params["item"]);
		unset($params["from"]);

		reset($collection);
		$counter = 0;
		while(list($_key,$_item) = each($collection)){
			if(isset($key)){ $smarty->assign($key,$_key); }
			if(isset($item)){ $smarty->assign($item,$_item); }
			$smarty->assign("__counter__",$counter);

			// zbytek parametru se naasignuje do sablonky jako v predhozim pripade
			reset($params);
			while(list($key,$value) = each($params)){
				$smarty->assign($key,$value);
			}

			$smarty->display($template_name);
			$counter++;
		}
	}

	$smarty->_tpl_vars = $original_tpl_vars; // vraceni puvodnich hodnot do smarty objectu
	Atk14Timer::Stop("helper function.render");
}
?>

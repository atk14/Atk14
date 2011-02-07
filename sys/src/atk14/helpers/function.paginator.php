<?
/**
 * Smarty {paginator} tag to make paging records simpler.
 *
 * Given number of records the tag generates links to access other pages of the record listing.
 *
 * Basic form of the tag is as follows:
 * <code>
 * {paginator total_amount=100 max_amount=10 from=search_from}  // zde definujeme nazev parametru, kterym se posunujeme ve vysledcich
 * </code>
 *
 * Plugin picks all needed parameters from $params.
 * The helper expects usage of query parameter 'from'.
 * You can redefine it by the tag parameter 'from' to anything you want.
 * Basically you can omit the tag parameter 'from':
 * <code>
 * {paginator total_amount=100 max_amount=10}
 * </code>
 *
 * Plugin also takes variables $total_amount and $max_amount from controller. This code is mostly enough:
 * <code>
 *	{paginator}
 * </code>
 *
 * Zkopirovano z ATK14 a upraveno pro potreby GR.
 *
 */

/**
 *
 * @param array $params
 * @param array $content
 *
 */
function smarty_function_paginator($params,&$smarty){
	if(isset($params["finder"])){
		$finder = $params["finder"];
	}elseif(isset($smarty->_tpl_vars["finder"])){
		$finder = $smarty->_tpl_vars["finder"];
	}

	if(isset($finder)){
		$total_amount = $finder->getTotalAmount();
		$max_amount = $finder->getLimit();
	}else{
		$total_amount = isset($params["total_amount"]) ? (int)$params["total_amount"] : (int)$smarty->_tpl_vars["total_amount"];
		$max_amount = isset($params["max_amount"]) ? (int)$params["max_amount"] : (int)$smarty->_tpl_vars["max_amount"];
	}
	$from_name = isset($params["from"]) ? $params["from"] : "from";

	if($max_amount<=0){ $max_amount = 50; } // defaultni hodnota - nesmi dojit k zacykleni smycky while

	$par = $smarty->_tpl_vars["params"]->toArray();

	
	$from = isset($par["$from_name"]) ? (int)$par["$from_name"] : 0;
	if($from<0){ $from = 0;}

	$out = array();

	if($total_amount<=$max_amount){
		if($total_amount>=4){
			$out[] = "<div class=\"paginator\">";
			$out[] = "<p>".sprintf(_("%s items total"),$total_amount)."</p>";
			$out[] = "</div>";
			
		}
		return join("\n",$out);
	}

	$out[] = "<div class=\"paginator\">";
	$out[] = "<ul>";

	$first_child = true;
	if($from>0){
		$par["$from_name"] = $from - $max_amount;
		$url = Atk14Utils::BuildLink($par,$smarty,array("connector" => "&amp;"));
		$out[] = "<li class=\"first-child prev\"><a href=\"$url\">"._("prev")."</a></li>";
		$first_child = false;
	}

	$cur_from = 0;
	$screen = 1;
	$steps = ceil($total_amount / $max_amount);
	$current_step = floor($from / $max_amount)+1; // pocitano od 1
	while($cur_from < $total_amount){
		$par["$from_name"] = $cur_from;
		$url = Atk14Utils::BuildLink($par,$smarty,array("connector" => "&amp;"));
		$_class = array();
		$cur_from==$from && ($_class[] = "active");
		$first_child && ($_class[] = "first-child") && ($first_child = false);

		if($steps==$current_step && $screen==$current_step){
			$_class[] = "last-child";
		}

		$_class = $_class ? " class=\"".join(" ",$_class)."\"" : "";

		if($cur_from==$from){
			$out[] = "<li$_class>$screen</li>";
		}else{
			$out[] = "<li$_class><a href=\"$url\">$screen</a></li>";
		}
		$screen++;

		if($screen>2 && $current_step>6 && $screen<$current_step-4 && $screen<$steps-10){
			$out[] = "<li class=\"skip\">...</li>";
			while($screen<$current_step-4 && $screen<$steps-10){ $screen++; }
		}

		if($screen>$current_step+4 && $steps-$screen>=2 && $screen>11){
			$out[] = "<li class=\"skip\">...</li>";
			while(($steps-$screen)>=2){ $screen++; }
		}

		$cur_from = ($screen-1) * $max_amount;
	}

	if(($from+$max_amount)<$total_amount){
		$par["$from_name"] = $from + $max_amount;
		$url = Atk14Utils::BuildLink($par,$smarty,array("connector" => "&amp;"));
		$out[] = "<li class=\"last-child next\"><a href=\"$url\">"._("next")."</a></li>";
	}

	$out[] = "</ul>";

	$out[] = "<p>".sprintf(_("%s items total"),$total_amount)."</p>";
	$out[] = "</div>";

	return join("\n",$out);
}
?>

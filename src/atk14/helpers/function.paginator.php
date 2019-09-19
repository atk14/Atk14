<?php
/**
 * Smarty {paginator} tag to make paging records simpler.
 *
 * Given number of records the tag generates links to access other pages of the record listing.
 *
 * Basic form of the tag is as follows:
 *
 * ```
 * {paginator finder=$finder}
 * ```
 *
 * You don't have to forward a finder when you have one in the $finder variable.
 *
 * ```
 * {paginator}
 * ```
 *
 * If you don't have an finder then...
 *
 * ```
 * {paginator total_amount=100 max_amount=10 from=search_from}  // zde definujeme nazev parametru, kterym se posunujeme ve vysledcich
 * ```
 *
 * Plugin picks all needed parameters from $params.
 * The helper expects usage of query parameter 'from'.
 * You can redefine it by the tag parameter 'from' to anything you want.
 * Basically you can omit the tag parameter 'from':
 * ```
 * {paginator total_amount=100 max_amount=10}
 * ```
 *
 * Plugin also takes variables $total_amount and $max_amount from controller. This code is mostly enough:
 * ```
 * {paginator}
 * ```
 *
 * Example of the parameter items_total_label usage:
 * ```
 * {paginator items_total_label="article total"}
 * ```
 *
 * @package Atk14\Helpers
 * @filesource
 */

/**
 *
 * Smarty function that generates set of links to other pages of a recordset.
 *
 * @param array $params
 * @param array $content
 */
function smarty_function_paginator($params,$template){
	$smarty = atk14_get_smarty_from_template($template);

	$params += array(
		"items_total_label" => _("items total"), // "articles", "products"...
	);

	if(isset($params["finder"])){
		$finder = $params["finder"];
	}elseif(!is_null($smarty->getTemplateVars("finder"))){
		$finder = $smarty->getTemplateVars("finder");
	}

	if(isset($finder)){
		$total_amount = $finder->getTotalAmount();
		$max_amount = $finder->getLimit();
	}else{
		$total_amount = isset($params["total_amount"]) ? (int)$params["total_amount"] : (int)$smarty->getTemplateVars("total_amount");
		$max_amount = isset($params["max_amount"]) ? (int)$params["max_amount"] : (int)$smarty->getTemplateVars("max_amount");
	}
	if($max_amount<=0){ $max_amount = 50; } // defaultni hodnota - nesmi dojit k zacykleni smycky while

	$_from = defined("ATK14_PAGINATOR_OFFSET_PARAM_NAME") ? ATK14_PAGINATOR_OFFSET_PARAM_NAME : "from";
	$from_name = isset($params["$_from"]) ? $params["$_from"] : "$_from";

	$items_total_label = $params["items_total_label"];

	$par = $smarty->getTemplateVars("params")->toArray();
	// There is a possibility to change action, controller, lang and namespace variables.
	// It is usefull when you display first page of some list on the frontpage and links from the paginator must point to an another controller/action.
	foreach(array("action","controller","lang","namespace") as $_k){
		if(isset($params[$_k])){ $par[$_k] = $params["action"]; }
	}

	
	$from = isset($par["$from_name"]) ? (int)$par["$from_name"] : 0;
	if($from<0){ $from = 0;}

	$out = array();

	if($total_amount<=$max_amount){
		if($total_amount>=5){
			$out[] = "<div class=\"pagination-container\">";
			$out[] = "<p><span class=\"badge badge-secondary\">".$total_amount."</span> ".$items_total_label."</p>";
			$out[] = "</div>";
			
		}
		return join("\n",$out);
	}

	$out[] = "<div class=\"pagination-container\">";
	$out[] = "<ul class=\"pagination\">";

	$first_child = true;
	if($from>0){
		$par["$from_name"] = $from - $max_amount;
		$url = _smarty_function_paginator_build_url($par,$smarty,$from_name);
		$out[] = "<li class=\"page-item first-child prev\"><a class=\"page-link\" href=\"$url\"><i class=\"fas fa-arrow-left\"></i> "._("Prev")."</a></li>";
		$first_child = false;
	}

	$cur_from = 0;
	$screen = 1;
	$steps = ceil($total_amount / $max_amount);
	$current_step = floor($from / $max_amount)+1; // pocitano od 1
	while($cur_from < $total_amount){
		$par["$from_name"] = $cur_from;
		$url = _smarty_function_paginator_build_url($par,$smarty,$from_name);
		$_class = array( "page-item" );
		$cur_from==$from && ($_class[] = "active");
		$first_child && ($_class[] = "first-child") && ($first_child = false);

		if($steps==$current_step && $screen==$current_step){
			$_class[] = "last-child";
		}

		$_class = $_class ? " class=\"".join(" ",$_class)."\"" : "";

		if($cur_from==$from){
			$out[] = "<li$_class><a class=\"page-link\" href=\"$url\">$screen</a></li>";
		}else{
			$out[] = "<li$_class><a class=\"page-link\" href=\"$url\">$screen</a></li>";
		}
		$screen++;
		
		// skipped items ...
		if($screen>2 && $current_step>6 && $screen<$current_step-4 && $screen<$steps-10){
			$out[] = "<li class=\"page-item skip disabled\"><span class=\"page-link\">&hellip;</span></li>";
			while($screen<$current_step-4 && $screen<$steps-10){ $screen++; }
		}
		
		if($screen>$current_step+4 && $steps-$screen>=2 && $screen>11){
			$out[] = "<li class=\"page-item skip disabled\"><span class=\"page-link\">&hellip;</span></li>";
			while(($steps-$screen)>=2){ $screen++; }
		}

		$cur_from = ($screen-1) * $max_amount;
	}

	if(($from+$max_amount)<$total_amount){
		$par["$from_name"] = $from + $max_amount;
		$url = _smarty_function_paginator_build_url($par,$smarty,$from_name);
		$out[] = "<li class=\"page-item last-child next\"><a class=\"page-link\" href=\"$url\">"._("Next")." <i class=\"fas fa-arrow-right\"></i></a></li>";
	}

	$out[] = "</ul>";

	$out[] = "<p><span class=\"badge badge-secondary\">".$total_amount."</span> ".$items_total_label."</p>";
	$out[] = "</div>";

	return join("\n",$out);
}

// removes from the $params offset when it equals to zero
function _smarty_function_paginator_build_url($params,&$smarty,$from_name){
	if(isset($params[$from_name]) && $params[$from_name]==0){
		unset($params[$from_name]);
	}
	return Atk14Utils::BuildLink($params,$smarty,array("connector" => "&amp;"));
}

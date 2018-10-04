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
	global $ATK14_GLOBAL;

	$smarty = atk14_get_smarty_from_template($template);

	$params += array(
		"finder" => null,
		//
		"total_amount" => $smarty->getTemplateVars("total_amount"),
		"max_amount" => $smarty->getTemplateVars("max_amount"),
		//
		"aria_label" => _("Pagination"),
		"bootstrap4" => USING_BOOTSTRAP4,
		"align" => "left", // "left", "rigth", "center"; it only matters when using Bootstrap4
	);

	$finder = null;
	if($params["finder"]){
		$finder = $params["finder"];
	}elseif(!is_null($smarty->getTemplateVars("finder"))){
		$finder = $smarty->getTemplateVars("finder");
	}
	//
	if($finder){
		$total_amount = $finder->getTotalAmount();
		$max_amount = $finder->getLimit();
	}else{
		$total_amount = (int)$params["total_amount"];
		$max_amount = (int)$params["max_amount"];
	}

	$_from = defined("ATK14_PAGINATOR_OFFSET_PARAM_NAME") ? ATK14_PAGINATOR_OFFSET_PARAM_NAME : "from";
	$from_name = isset($params["$_from"]) ? $params["$_from"] : "$_from";

	$bootstrap4 = $params["bootstrap4"];

	if($max_amount<=0){ $max_amount = 50; } // defaultni hodnota - nesmi dojit k zacykleni smycky while

	$par = $smarty->getTemplateVars("params")->toArray();

	if($smarty->getTemplateVars("rendering_component")){
		$par["namespace"] = $smarty->getTemplateVars("prev_namespace");
		$par["controller"] = $smarty->getTemplateVars("prev_controller");
		$par["action"] = $smarty->getTemplateVars("prev_action");
	}

	// There is a possibility to change action, controller, lang and namespace variables.
	// It is usefull when you display first page of some list on the frontpage and links from the paginator must point to an another controller/action.
	foreach(array("action","controller","lang","namespace") as $_k){
		if(isset($params[$_k])){ $par[$_k] = $params[$_k]; }
	}

	
	$from = isset($par["$from_name"]) ? (int)$par["$from_name"] : 0;
	if($from<0){ $from = 0;}

	$out = array();

	if($bootstrap4){
		$ul_class = array('pagination');
		$p_class = array('pager__items-count');
		($params["align"]=="center") && ($ul_class[] = "justify-content-center") && ($p_class[] = 'text-center');
		($params["align"]=="right") && ($ul_class[] = "justify-content-end") && ($p_class[] = 'text-right');
		$ul_class = $ul_class ? ' class="'.join(" ",$ul_class).'"' : '';
		$p_class = $p_class ? ' class="'.join(" ",$p_class).'"' : '';
	}else{
		$ul_class = $p_class = '';
	}

	if($total_amount<=$max_amount){
		if($total_amount>=5){
			if($bootstrap4){
				$out[] = sprintf('<nav class="pager" aria-label="%s">',h($params["aria_label"]));
				$out[] = "<p$p_class>".sprintf(_("%s items total"),$total_amount)."</p>";
				$out[] = "</nav>";
			}else{
				$out[] = "<div class=\"paginator\">";
				$out[] = "<p>".sprintf(_("%s items total"),$total_amount)."</p>";
				$out[] = "</div>";
			}
		}
		return join("\n",$out);
	}

	$out[] = $bootstrap4 ? sprintf('<nav class="pager" aria-label="%s">',h($params["aria_label"])) : "<div class=\"paginator\">";
	$out[] = $bootstrap4 ? "<ul$ul_class>" : "<ul>";

	$first_child = true;
	if($from>0){
		$par["$from_name"] = $from - $max_amount;
		$url = _smarty_function_paginator_build_url($par,$smarty,$from_name);
		$out[] = $bootstrap4 ? '<li class="page-item"><a class="page-link" href="'.$url.'" tabindex="-1">'._("prev").'</a></li>': "<li class=\"first-child prev\"><a href=\"$url\">"._("prev")."</a></li>";
		$first_child = false;
	}

	$hellip_element = $bootstrap4 ? '<li class="page-item page-item--hellip">&hellip;</li>' : '<li class="skip">...</li>';
	$cur_from = 0;
	$screen = 1;
	$steps = ceil($total_amount / $max_amount);
	$current_step = floor($from / $max_amount)+1; // pocitano od 1
	while($cur_from < $total_amount){
		$par["$from_name"] = $cur_from;
		$url = _smarty_function_paginator_build_url($par,$smarty,$from_name);

		$_class = array();
		$bootstrap4 && ($_class[] = "page-item");
		($cur_from==$from) && ($_class[] = "active");
		if($first_child){
			!$bootstrap4 && ($_class[] = "first-child");
			$first_child = false;
		}
		if(!$bootstrap4 && $steps==$current_step && $screen==$current_step){
			$_class[] = "last-child";
		}

		$_class = $_class ? " class=\"".join(" ",$_class)."\"" : "";

		if($cur_from==$from){
			$out[] = $bootstrap4 ? '<li'.$_class.'><span class="page-link">'.$screen.' <span class="sr-only">('._("current page").')</span></span></li>' : "<li$_class>$screen</li>";
		}else{
			$out[] = $bootstrap4 ? '<li'.$_class.'><a class="page-link" href="'.$url.'">'.$screen.'</a></li>' : "<li$_class><a href=\"$url\">$screen</a></li>";
		}
		$screen++;

		if($screen>2 && $current_step>6 && $screen<$current_step-4 && $screen<$steps-10){
			$out[] = $hellip_element;
			while($screen<$current_step-4 && $screen<$steps-10){ $screen++; }
		}

		if($screen>$current_step+4 && $steps-$screen>=2 && $screen>11){
			$out[] = $hellip_element;
			while(($steps-$screen)>=2){ $screen++; }
		}

		$cur_from = ($screen-1) * $max_amount;
	}

	if(($from+$max_amount)<$total_amount){
		$par["$from_name"] = $from + $max_amount;
		$url = _smarty_function_paginator_build_url($par,$smarty,$from_name);
		$out[] = $bootstrap4 ? '<li class="page-item"><a class="page-link" href="'.$url.'">'._("next").'</a></li>' : "<li class=\"last-child next\"><a href=\"$url\">"._("next")."</a></li>";
	}

	$out[] = "</ul>";

	$out[] = "<p$p_class>".sprintf(_("%s items total"),$total_amount)."</p>";
	$out[] = $bootstrap4 ? '</nav>' : '</div>';

	return join("\n",$out);
}

// removes from the $params offset when it equals to zero
function _smarty_function_paginator_build_url($params,&$smarty,$from_name){
	if(isset($params[$from_name]) && $params[$from_name]==0){
		unset($params[$from_name]);
	}
	return Atk14Utils::BuildLink($params,$smarty,array("connector" => "&amp;"));
}

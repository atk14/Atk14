<?
/**
* Vygeneruje strankovac vysledku.
* Ocekava se, ze se posunuje parametrem from (mozno predefinovat).
*	
* Plugin si vyzobe vsechny parametry ze slovniku $params.
* 
* {paginator total_amount=100 max_amount=10}
* {paginator total_amount=100 max_amount=10 from=search_from}  // zde definujeme nazev parametru, kterym se posunujeme ve vysledcich
*
* Paginator si sam prebira i total_amount a max_amount. Proto nasledujici staci:
*
*	{paginator}
*
*/
function smarty_function_paginator($params,&$smarty){
	$total_amount = isset($params["total_amount"]) ? (int)$params["total_amount"] : (int)$smarty->_tpl_vars["total_amount"];
	$max_amount = isset($params["max_amount"]) ? (int)$params["max_amount"] : (int)$smarty->_tpl_vars["max_amount"];
	$from_name = isset($params["from"]) ? $params["from"] : "from";

	if($max_amount<=0){ $max_amount = 50; } // defaultni hodnota - nesmi dojit k zacykleni smycky while

	$par = $smarty->_tpl_vars["params"]->toArray();

	
	$from = isset($par["$from_name"]) ? (int)$par["$from_name"] : 0;
	if($from<0){ $from = 0;}

	$out = array();

	if($total_amount<=$max_amount){ return ""; }


	$out[] = "<ul id=\"paginator\">";

	if($from>0){
		$par["$from_name"] = $from - $max_amount;
		$url = Atk14Utils::BuildLink($par,$smarty,array("connector" => "&amp;"));
		$out[] = "<li class=\"first-child\"><a href=\"$url\">&laquo; "._("předchozí")."</a></li>";
	}else{
		$out[] = "<li class=\"no-link first-child\">&laquo; "._("předchozí")."</li>";
	}

	$cur_from = 0;
	$screen = 1;
	$steps = ceil($total_amount / $max_amount);
	$current_step = floor($from / $max_amount)+1; // pocitano od 1
	while($cur_from < $total_amount){
		$par["$from_name"] = $cur_from;
		$url = Atk14Utils::BuildLink($par,$smarty,array("connector" => "&amp;"));
		$_class = $cur_from == $from ? " class=\"active\"" : "";
		$out[] = "<li$_class><a href=\"$url\">$screen</a></li>";
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
		$out[] = "<li class=\"last-child\"><a href=\"$url\">"._("další")." &raquo;</a></li>";
	}else{
		$out[] = "<li class=\"no-link last-child\">"._("další")." &raquo;</li>";
	}

	$out[] = "</ul>";

	return join("\n",$out);
}
?>

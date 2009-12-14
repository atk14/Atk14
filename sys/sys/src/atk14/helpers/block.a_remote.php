<?
/**
* 
*/
function smarty_block_a_remote($params, $content, &$smarty, &$repeat)
{
	$attributes = array();

	$params = array_merge(array(
		"_method" => "get",
		"__be_pretty_ugly__" => false // internal parameter, don't use it outside
	),$params);

	$be_pretty_ugly = $params["__be_pretty_ugly__"];
	unset($params["__be_pretty_ugly__"]);

	$method = strtolower($params["_method"]);
	unset($params["_method"]);

	$url = Atk14Utils::BuildLink($params,$smarty);

	$attrs = Atk14Utils::ExtractAttributes($params);
	$attrs["href"] = $url;
	if($be_pretty_ugly){
		// TODO: doesn't check existance of the JS function before_remote_link()
		$_data = "";
		$_type = "GET";
		if($method=="post"){
			$_data = ", data: ''";
			$_type = "POST";
		}
		$attrs["onclick"] = "$('body').css('cursor','wait'); $.ajax({ type: '$_type', url: $(this).attr('href')$_data, dataType: 'script', complete: function(){ $('body').css('cursor','default'); } }); return false;";
	}else{
		if(!isset($attrs["class"])){ $attrs["class"] = ""; }
		$_class = $method=="post" ? "remote_link post" : "remote_link";
		$attrs["class"] = trim("$attrs[class] $_class");
	}

	$attrs = Atk14Utils::JoinAttributes($attrs);

	//$attributes[] = " onclick=\"JavaScript: jQuery.getScript($(this).attr('href')); return false;\"";
	//$attributes[] = " onclick=\"JavaScript: document.body.style.cursor='wait'; $.ajax({type: 'GET', url: $(this).attr('href'), dataType: 'script', complete: function(){ document.body.style.cursor='default'; } }); return false;\"";

	// prepne cursor na wait; provede ajax request; zmeni cursor na default

	return "<a$attrs>$content</a>";
}
?>

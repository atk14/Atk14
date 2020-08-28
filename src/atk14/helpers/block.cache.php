<?php
/**
 * Provides caching for the given template block
 *
 * There are 3 main parameters: key, expire and if.
 * Other paramaters just salt the key.
 * 
 * Basic usage:
 *	{cache}
 *		{render partial="some_template"}
 *	{/cache}
 *
 *	{cache key="top_menu"}
 *		<ul>...</ul>
 *	{/cache}
 *
 * Cached content should be valid for 10 minutes
 *	{cache key="top_menu" expire=600}
 *		<ul>...</ul>
 *	{/cache}
 *
 * Cached content differs for admin users
 *	{cache key="top_menu" expire=600 is_admin=($logged_user && $logged_user->isAdmin())}
 *		<ul>...</ul>
 *	{/cache}
 *
 * Do not cache for logged user
 *	{cache if=!$logged_user key="menu"}
 *		<ul>...</ul>
 *	{/cache}
 *
 * If a content distinguish for different languages, don't forget to add a salting parameter lang along with the specific key.
 *	{cache key="menu" lang=$lang}
 *		<ul>...</ul>
 *	{/cache}
 */
function smarty_block_cache($params,$content,$template,&$repeat){
	$smarty = atk14_get_smarty_from_template($template);

	$params += array(
		"if" => true,
		"key" => null,
		"expire" => 60, // 60 sec
	);

	$expire = $params["expire"]; unset($params["expire"]);
	$key = $params["key"]; unset($params["key"]);
	$if = $params["if"]; unset($params["if"]);

	if(!$if){
		// cache is not enabled
		if($repeat){ return; }
		return $content;
	}

	if(!strlen($key)){
		$current_template = $smarty->_current_file; // "/home/bob/devel/project_x/app/views/shared/_menu.tpl"
		if(strlen($current_template)==0){
			throw new Exception('smarty_block_cache: $current_template is empty string, specify parameter "key" on the given {cache} block');
		}

		$lang = $smarty->getTemplateVars("lang");
		$namespace = $smarty->getTemplateVars("namespace");
		$controller = $smarty->getTemplateVars("controller");
		$action = $smarty->getTemplateVars("action");

		$tpl_nice_name = preg_replace('/.*\/([^\/]+)$/','\1',$current_template); // "/home/bob/devel/project_x/app/views/shared/_menu.tpl" -> _menu.tpl
		$key = "$namespace.$lang.$controller.$action.$tpl_nice_name.".md5($current_template);
	}

	// $key sanitization
	$key = preg_replace('/[^a-zA-Z0-9._]/','_',$key);
	$key = preg_replace('/^\./','_.',$key); // ".en.main.index" -> "_.en.main.index" ; "." -> "_."

	$salt = sizeof($params) ? "_".md5(serialize($params)) : ""; // other parameters make a salt

	$cache_dir = TEMP.'/content_caches';
	!file_exists($cache_dir) && Files::Mkdir($cache_dir);
	$cache_file = TEMP."/content_caches/$key$salt";

	if($repeat){
		if(file_exists($cache_file) && time()-filemtime($cache_file)<$expire){
			// reading cache
			$repeat = false;
			return Files::GetFileContent($cache_file);
		}

		return;
	}

	$tmp_file = Files::WriteToTemp($content,$err,$err_str);
	if($err){
		throw new Exception("Unable to write cache file for a content: $err_str");
	}
	
	Files::MoveFile($tmp_file,$cache_file);

	return $content;
}

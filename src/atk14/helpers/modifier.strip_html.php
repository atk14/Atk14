<?php
function smarty_modifier_strip_html($content){
	// the following tags are removed with their content
	$tags = array(
		"head",
		"style",
		"script",
		"object",
		"embed",
		"applet",
		"noframes",
		"noscript",
		"noembed",
	);
	$tags = join('|',$tags);
	$content = preg_replace("#<($tags)[^>]*?>.*?</\\1>#siu"," ", $content);

	// remove inline tags
	$inline_tags = array(
		"a",
		"abbr",
		"acronym",
		"b",
		"bdo",
		"big",
		"br",
		"button",
		"cite",
		"code",
		"dfn",
		"em",
		"i",
		"img",
		"input",
		"kbd",
		"label",
		"map",
		"object",
		"output",
		"q",
		"samp",
		"script",
		"select",
		"small",
		"span",
		"strong",
		"sub",
		"sup",
		"textarea",
		"time",
		"tt",
		"var",
	);
	$inline_tags = join('|',$inline_tags);
	$content = preg_replace("#<($inline_tags)(|\\s[^>]*?)>#si","",$content);
	$content = preg_replace("#</($inline_tags)>#si","",$content);

	//
	$content = preg_replace('#<[^>]*?>#s',' ',$content);

	$content = html_entity_decode($content); // e.g. "&amp;" -> "&"

	$content = trim($content);
	$content = preg_replace('#[\t\r\n]#',' ',$content);
	$content = preg_replace('#\s{2,}#',' ',$content);

	return $content;
}

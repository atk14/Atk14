<?php
function smarty_modifier_strip_tags($content){
	$inline_elements = array(
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
	$inline_elements = join('|',$inline_elements);
	$content = preg_replace("/<($inline_elements)(|\\s[^>]*)>/si","",$content);
	$content = preg_replace("/<\\/($inline_elements)>/si","",$content);

	//
	$nl = '[\r\n]';
	$s = '[ \t]*'; // space or tab
	$w = '\s*'; // white space
	$tag = '<[^>]*>';

	$content = preg_replace("/($w$tag$s$w)+$/s",'',$content); // at the end of the document
	$content = preg_replace("/^($w$tag$s$w)/s",'',$content); // at the beginning of the document

	$content = preg_replace("/($nl)($s$tag$s)+($nl)/",'\1\3',$content); // the only tag(s) on a line

	$content = preg_replace("/($s$tag$s)+($nl)/",'\2',$content); // at the end of line
	$content = preg_replace("/($nl)($s$tag$s)+/s",'\1',$content); // at the beginning of line

	$content = preg_replace("/$tag/s",' ',$content);

	$content = html_entity_decode($content); // e.g. "&amp;" -> "&"

	return $content;
}

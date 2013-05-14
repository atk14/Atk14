<?php
// otestuje funkcnost zkracenych nazvu metod...
$src = Files::GetFileContent("tc_xmole.php");
$src = strtr($src,array(
	"tc_xmole" => "tc_shortcuts",
	"get_element_data" => "get_data",
	"get_attribute_value" => "get_attribute",
	"get_xmole_by_first_matching_branch" => "get_xmole",
	"get_xmoles_by_all_matching_branches" => "get_xmoles",
	"<?php" => "",
	"?>" => "",
	"<?" => "",
	"?>" => ""
));
eval($src);

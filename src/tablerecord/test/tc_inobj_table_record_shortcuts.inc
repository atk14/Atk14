<?php
// nacte obsah souboru tc_inobj_table_record.inc
$content = file_get_contents(dirname(__FILE__)."/tc_inobj_table_record.inc");

// zmeni nazev tridy
$content = str_replace("class tc_inobj_table_record","class tc_inobj_table_record_shortcuts",$content);

// volani getValue(), setValue(), setValues() zmeni na g() a s()
$content = str_replace("getValue(","g(",$content);
$content = str_replace("setValue(","s(",$content);
$content = str_replace("setValues(","s(",$content);

// provede eval
$content = str_replace('<?php',"",$content);
eval($content);

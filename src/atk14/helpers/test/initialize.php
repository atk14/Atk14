<?php
define("TEST",true);
setlocale(LC_NUMERIC,"C");
require(__DIR__."/../../../string4/load.php");
require(__DIR__."/../../../translate/load.php");
require(__DIR__."/../function.to_json.php");
require(__DIR__."/../modifier.camelize.php");
require(__DIR__."/../modifier.to_sentence.php");
require(__DIR__."/../modifier.count.php");
require(__DIR__."/../modifier.slugify.php");
require(__DIR__."/../modifier.format_number.php");
require(__DIR__."/../block.javascript_tag.php");
require_once(__DIR__."/../block.no_spam.php");
require_once(__DIR__."/../modifier.no_spam.php");
require_once(__DIR__."/../modifier.strip_html.php");
require_once(__DIR__."/../block.strip_html.php");
require(__DIR__."/../modifier.strlen.php");
require_once(__DIR__."/../block.jstring.php");
require(__DIR__."/../block.sortable.php");
require(__DIR__."/../block.nl2br.php");

require(__DIR__."/../../../functions.php");
require(__DIR__."/../block.replace_html.php");
require(__DIR__."/../../atk14_utils.php");
require(__DIR__."/../../atk14_locale.php");
require(__DIR__."/../../atk14_require.php");

require(__DIR__."/../modifier.date.php");
require(__DIR__."/../modifier.json_encode.php");
require(__DIR__."/../modifier.sizeof.php");
require(__DIR__."/../modifier.array_filter.php");
require(__DIR__."/../modifier.preg_split.php");
require(__DIR__."/../modifier.join.php");
require(__DIR__."/../modifier.html_entity_decode.php");
require(__DIR__."/../modifier.constant.php");

require(__DIR__."/../modifier.strtolower.php");
require(__DIR__."/../modifier.strtoupper.php");

require(__DIR__."/../block.trim.php");
require(__DIR__."/../modifier.trim.php");

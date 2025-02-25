<?php
function smarty_function_increment_counter($params, $template) {
	$template->getTemplateVars('test')->counter++;
}

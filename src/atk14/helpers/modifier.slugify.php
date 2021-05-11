<?php
/**
 * Converts the given string into a slug
 *
 *
 * ```
 * {"Don't cross the streams"|slugify} {* don-t-cross-the-streams *}
 * {"Don't cross the streams"|slugify:8} {* don-t-cr *}
 * ```
 *
 * @param string $string
 * @param integer $max_length (default null)
 */
function smarty_modifier_slugify($string,$max_length = null){
	return String4::ToObject($string)->toSlug($max_length)->toString();
}

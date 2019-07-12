<?php

/**
 * Converts 'first_name' to 'First name'.
 *
 * @param string $name
 * @return string
 */
function pretty_name($name)
{
	return str_replace('_', ' ', ucfirst($name));
}

/**
 * Escapes html.
 */
function forms_htmlspecialchars($string)
{
	return h($string, ENT_COMPAT);

	// puvodne tady byla tato verze.
	// to ale zlobilo na webech, ktere nebezi v UTF-8...
	//return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

/**
 * A smarter replacement for PHP built-in array_merge().
 *
 * In PHP5 array_merge() changes its behaviour and accepts strictly arrays.
 */
function forms_array_merge ()
{
	$merged=array ();
	for($i=0;$i<func_num_args ();$i++)
	{
		$val = func_get_arg($i);
		if(!isset($val)){ continue; }
		$tmp= is_array($val) ?  $val : array($val);
		$merged=array_merge ($merged,$tmp);
	}
	return $merged;
}

/**
* Funkce pro prevod pole s error hlaskami na HTML strukturu
* vhodnou k zobrazeni v sablonach.
*/
function format_errors($errors)
{
	if (count($errors) < 1) {
		return '';
	}
	$output = array();
	foreach ($errors as $e) {
		$output[] = '<li>'.$e.'</li>';
	}
	return '<ul class="errorlist">'.implode('', $output).'</ul>';
}

/**
 * Converts an array to HTML attributes.
 *
 * Small helper function.
 * Hodnoty jsou escapovany, klice se neescapuji.
 *
 * Example. This array
 * <code>
 *   array('src':'1.jpg', 'alt':'obrazek')
 * </code>
 *
 * will be converted to
 * <code>
 *   src="1.jpg" alt="obrazek"
 * </code>
 *
 * @param array $attrs
 * @return string
 */
function flatatt($attrs)
{
	$out = array();
	foreach ($attrs as $k => $v) {
		$out[] = ' '.$k.'="'.forms_htmlspecialchars($v).'"';
	}
	return implode('', $out);
}

/**
 * Merges arrays.
 *
 * Unlike {@link array_merge} my_array_merge first converts keys to integers.
 * If a key is available in two arrays (in first array as 1 (integer) and in the second array as "1" (string))
 * then the value from the second array overrides the value from the first array.
 *
 * <code>
 * $ary_data = array(
 *  array(
 *      "1" => "banana",
 *      "2" => "lemon",
 *      "3" => "orange"
 *      ),
 *  array(
 *      1 => "yellow banana",
 *      "4" => "pineapple",
 *      "5" => "apple"
 *      ));
 * $new_data = my_array_merge($ary_data);
 * </code>
 * 
 * Result:
 * <code>
 *  array(
 *      "1" => "yellow banana",
 *      "2" => "lemon",
 *      "3" => "orange",
 *      "4" => "pineapple",
 *      "5" => "apple"
 *      )
 * </code>
 *
 * @param array $data
 * @return array
 */
function my_array_merge($data)
{
	$output = array();
	foreach ($data as $item) {
		foreach ($item as $k => $v) {
			$output[(string)$k] = $v;
		}
	}
	return $output;
}

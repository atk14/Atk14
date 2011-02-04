<?
/**
 * Smarty {content}{/content} block tag.
 *
 *
 * Stores a block of code for later use. You can use multiple calls to store more content. The stored block will be rendered by using {placeholder} tag.
 * For more info see {@link smarty_function_placeholder()}
 *
 * <code>
 * {content for="javascript"}
 * <script language="javascript" type="text/javascript">
 *  function welcome() {
 *    alert('welcome human');
 *  }
 * </script>
 * {/content}
 *
 * </code>
 *
 * @package Atk14
 * @subpackage Helpers
 */

/**
 * @param array $params the only parameter is <b>for</b> which identifies the place to store the block
 * @param array $content
 *
 */
function smarty_block_content($params,$content,&$smarty,&$repeat){
	$id = $params["for"];

	if(!isset($smarty->atk14_contents)){ $smarty->atk14_contents = array(); }
	if(!isset($smarty->atk14_contents[$id])){ $smarty->atk14_contents[$id] = ""; }

	$smarty->atk14_contents[$id] .= $content;

	return "";
}
?>

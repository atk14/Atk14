{assign var=some_value value="LOWER_VALUE"}
<li class="{$class}">{$label}: {$book.title} by {$book.author} (nr#{$__counter__})</li>

<!--
{render partial=some_value_partial}
(some_value from the middle: {$some_value}){* expecting LOWER_VALUE *}
-->



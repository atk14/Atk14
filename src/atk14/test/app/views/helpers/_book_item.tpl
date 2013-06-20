{assign var=some_value value="LOWER_VALUE"}
<li class="{$class}">{$label}: {$book.title} by {$book.author} (index={$__index__}, {$__iteration__}/{$__total__}{if $__first__}, first{/if}{if $__last__}, last{/if})</li>

<!--
{render partial=some_value_partial}
(some_value from the middle: {$some_value}){* expecting LOWER_VALUE *}
-->



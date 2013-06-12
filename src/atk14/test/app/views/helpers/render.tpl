{assign var=label value="ADVENTURE"}
{assign var=some_value value="TOP_VALUE"}

<ul>
{render partial=book_item from=$books item=book class="red"}
</ul>

some_value: {$some_value}


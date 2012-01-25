<h2>{$page_title|h}</h2>

{capture assign=label}{t}Update creature{/t}{/capture}
{render partial=create_edit_form button_label=$label}

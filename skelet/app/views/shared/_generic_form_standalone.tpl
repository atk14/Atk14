{render partial="shared/form_error"}

{form}
<fieldset>
	{render partial="shared/form_field" fields=$form->get_field_keys()}
	<div class="buttons">
		<button type="submit">{if $button_text}{$button_text}{else}{t}Save{/t}{/if}</button>
	</div>
</fieldset>
{/form}


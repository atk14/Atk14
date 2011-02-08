{render partial=shared/form_error}

{form}
	<fieldset>
		{render partial=shared/form_field fields=name,description,image_url}
		<div class="buttons">
			<button type="submit">{$button_label}</button>
		</div>
	</fieldset>
{/form}

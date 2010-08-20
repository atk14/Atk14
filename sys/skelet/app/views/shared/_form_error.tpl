{*
 * Vytiskne poznamku o chybe ve formulari.
 *
 *	 {render partial=shared/form_error}
 *	 {render partial=shared/form_error form=$update_form}
 *
 * 
 * Pokud je nastaven parametr small_form,
 * nebude se nic tisknout, pokud formular neobsahuje non_field_errors.
 * U malych formularu totiz uplne staci, kdyz se cervene zvyrazni pouze chybove pole.
 * 
 *	{render partial=shared/form_error small_form=1}
 * 
 *}
{if $form->has_errors()}
		{if $form->non_field_errors()}
			{*<div class="mainerr">*}
				{if sizeof($form->non_field_errors())>1}
					{* pokud mame vice chyb, vygenerujeme seznam <ul><li> *}
					<div class="error">
						<p>
							<em>{t}Pøi zpracování formuláøe nastaly následující potíže:{/t}</em>
						</p>
						<ul>
							{render partial=shared/form_error_item from=$form->non_field_errors() item=error}
						</ul>
					</div>
				{else}
					{* s jedinou chybou je to veselejsi... *}
					{assign var=errors value=$form->non_field_errors()}
					<p class="error">
						<em>{$errors.0|h}</em>
					</p>
				{/if}
			{*</div>*}
		{elseif !$small_form}
			{*<div class="mainerr">*}
				<p class="error">
					<em>{t}Nìkterá z položek byla špatnì vyplnìna. Prosím, zkontrolujte formuláø a opravte chyby.{/t}</em>
				</p>
			{*</div>*}
		{/if}
{/if}

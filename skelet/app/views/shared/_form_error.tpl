{*
 * Displays form`s error heading.
 *
 *	 {render partial="shared/form_error"}
 *	 {render partial="shared/form_error" form=$update_form}
 * 
 * If there is the small_form parameter,
 * no output will be produced unless there is a "non field" error.
 * In case of small forms this is enough that error fileds are highlighted.
 *  
 *	{render partial="shared/form_error" small_form=1}
 *}

{if $form->has_errors()}
		{if $form->non_field_errors()}
				{if sizeof($form->non_field_errors())>1}
					{* if there are more erorrs *}
					<div class="error">
						<p>
							<em>{t}The following difficulties have occurred during the form processing:{/t}</em>
						</p>
						<ul>
							{render partial="shared/form_error_item" from=$form->non_field_errors() item=error}
						</ul>
					</div>
				{else}
					{* if there is only one error *}
					{assign var=errors value=$form->non_field_errors()}
					<p class="error">
						<em>{$errors.0 nofilter}</em>
					</p>
				{/if}
		{elseif !$small_form}
				<p class="error">
					<em>{t}Some of the items were filled incorrectly. Please, check the form and correct the errors.{/t}</em>
				</p>
		{/if}
{/if}

{*
 * Displays a form field
 * {render partial="shared/form_field" field="title"}
 * {render partial="shared/form_field" field=$form->get_field("title")}
 *
 * Generuje toto:
 * <div>
 *		<label>Label</label>
 * 		<input />
 *		<div class="help">
 *			Help Text
 *			<p class="hint">Example: a correct value example</p>
 *		</div>
 *		<ul class="error_list">
 *			<li>Error text #1</li>
 *			<li>Error text #2</li>
 *		</ul>
 * </div>
 *
 * Parent divu jsou automaticky podle potreb nastavovany tridy:
 *  - required
 *  - error
 * 
 * Pokud ma mit div dalsi tridu (tridy), je mozne predat parametr class: 
 * {render partial="shared/form_field" field=title class="blue"}
 *
 * Dale mozno predat parametr hide_label=1, potom se tagu <label> prida trida access a tim se skryje:
 * {render partial="shared/form_field" field=title hide_label=1}
 *
 * Printing out more fields on a single line
 * {render partial="shared/form_field" fields="firstname,lastname,email"}
 *
 * When all the fields are required, there`s no need to mark them as required
 * {render partial="shared/form_field" fields="firstname,lastname,email" omit_required=1}
 *}

{if $field}

	{if is_string($field)}
		{if !$form->has_field($field)}
			{error_log}The form doesn't contain field {$field}{/error_log}
		{/if}
		{assign var=field value=$form->get_field($field)}
	{/if}

	{assign var=required value=$field->required}
	{if $omit_required}
		{assign var=required value=0}
	{/if}

	<div{if $required || $field->errors() || $class} class="{trim}{if $required}required{/if}{if $field->errors()} error{/if}{if $class} {$class}{/if}{/trim}"{/if}>
		{if $reverse}{$field->as_widget() nofilter}{/if}
		<label for="{$field->id_for_label()}"{if $hide_label} class="access"{/if}>{$field->label}{if $required}<span> ({t}required{/t})</span>{/if}</label>
		{if !$reverse}{$field->as_widget() nofilter}{/if}

		{if $field->help_text || $field->hint}
			<div class="help">
				{if $field->help_text}<p>{$field->help_text nofilter}</p>{/if}
				{if $field->hint}
					<p class="hint"><strong>{t}Example:{/t}</strong> {$field->hint nofilter}</p>
				{/if}
			</div>
		{/if}

		{if $field->errors()}
			<ul class="error_list">
				{foreach from=$field->errors() item=err_item}
					<li>{$err_item nofilter}</li>
				{/foreach}
			</ul>
		{/if}
	</div>

{else}

	{if isset($fields) && is_string($fields)}
		{assign var=fields value=","|explode:$fields} {* using PHP function as a smarty modifier! *}
	{/if}

	{if isset($fields) && is_array($fields)}
		{foreach from=$fields item=field}
			{render partial="shared/form_field" field=$field}
		{/foreach}

	{/if}

{/if}

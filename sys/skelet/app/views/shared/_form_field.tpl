{*
 * Vytiskne dane formularove pole:
 * {render partial=shared/form_field field=$form->get_field("title")}
 * {render partial=shared/form_field field=title}
 *
 * Generuje toto:
 * <div>
 *		<label>Label</label>
 * 		<input />
 *		<div class="help">
 *			Help Text
 *		</div>
 *		<ul class="error_list">
 *			<li>Error Text</li>
 *		</ul>
 * </div>
 *
 * Parent divu jsou automaticky podle potreb nastavovany tridy:
 *  - required
 *  - error
 * 
 * Pokud ma mit div dalsi tridu (tridy), je mozne predat parametr class: 
 * {render partial=shared/form_field field=$form->get_field("title") class="blue"}
 *
 * Dale mozno predat parametr hide_label=1, potom se tagu <label> prida trida access a tim se skryje:
 * {render partial=shared/form_field field=$form->get_field("title") hide_label=1}
 *}

{if is_string($fields) && strlen($fields)>0}
	{php}
		$this->_tpl_vars["_fields"] = explode(",",$this->_tpl_vars["fields"]);
		unset($this->_tpl_vars["fields"]);
	{/php}
	{render partial=shared/form_field from=$_fields item=field}
{elseif is_array($fields)}
	{foreach from=$fields item=field}
		{render partial=shared/form_field field=$field fields=null}
	{/foreach}
{else}
	{if is_string($field)}{assign var=field value=$form->get_field($field)}{/if}

	<div{if $field->required || $field->errors() || $class} class="{trim}{if $field->required}required{/if}{if $field->errors()} error{/if}{if $class} {$class}{/if}{/trim}"{/if}>
		{if $reverse}{$field->as_widget()}{/if}
		<label for="{$field->id_for_label()}"{if $hide_label} class="access"{/if}>{$field->label}{if $field->required}<span> ({t}required{/t})</span>{/if}</label>
		{if !$reverse}{$field->as_widget()}{/if}

		{if $field->help_text || $field->hint}
			<div class="help">
				{if $field->help_text}<p>{$field->help_text}</p>{/if}
				{if $field->hint}
					<p class="hint"><strong>{t}Example:{/t}</strong> {$field->hint}</p>
				{/if}
			</div>
		{/if}

		{if $field->errors()}
			<ul class="error_list">
				{foreach from=$field->errors() item=err_item}
					<li>{$err_item}</li>
				{/foreach}
			</ul>
		{/if}
	</div>
{/if}

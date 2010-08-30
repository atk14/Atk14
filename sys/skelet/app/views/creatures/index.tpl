<h2>{t}Listing creatures{/t}</h2>

{form}
	<fieldset>
		{render partial=shared/form_field field=q}

		<div class="buttons">
			<button type="submit">{t}Search{/t}</button>
		</div>
	</fieldset>
{/form}

<p>{a action=create_new}{t}Create a new creature{/t}{/a}</p>

{if $finder}
	{if $finder->isEmpty()}
		<p>{t}Nothing was found.{/t}</p>
	{else}

		<table>
			<thead>
				<tr>
					<th>{t}Id{/t}</th>
					<th>{t}Name{/t}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{render partial=creature_item from=$finder->getRecords() item=creature}
			</tbody>
		</table>

		{paginator}

	{/if}
{/if}

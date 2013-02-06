<h2>{$page_title}</h2>

<table>
	<tbody>
		<tr>
			<th>{t}Name{/t}</th>
			<td>{$creature->getName()}</td>
		</tr>
		<tr>
			<th>{t}Description{/t}</th>
			<td>{$creature->getDescription()|nl2br}</td>
		</tr>
		<tr>
			<th>{t}Image{/t}</th>
			<td>
				{if $creature->hasImage()}
					<img src="{$creature->getImageUrl()}" alt="{t}An image of the creature{/t}" />
				{else}
					&mdash;
				{/if}
			</td>
		</tr>
	</tbody>
</table>

<ul>
<li>{a action=detail id=$creature format=json}{t}Get the creature as JSON{/t}{/a}</li>
<li>{a action=detail id=$creature format=xml}{t}Get the creature as XML{/t}{/a}</li>
</ul>

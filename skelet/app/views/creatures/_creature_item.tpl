<tr>
	<td>{$creature->getId()}</td>
	<td>{$creature->getName()|h}</td>
	<td>
		{a action=detail id=$creature}{t}detail{/t}{/a} |
		{a action=edit id=$creature}{t}edit{/t}{/a} |
		{a_remote id=$creature _method=post _class=confirm}{t}destroy{/t}{/a_remote}
	</td>
</tr>

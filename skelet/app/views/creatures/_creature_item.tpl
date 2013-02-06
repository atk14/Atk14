{capture assign=confirm_message}{t id=$creature->getId()}Are you sure to destroy the creature #%1?{/t}{/capture}
<tr>
	<td>{$creature->getId()}</td>
	<td>{$creature->getName()}</td>
	<td>
		{a action=detail id=$creature}{t}detail{/t}{/a} |
		{a action=edit id=$creature}{t}edit{/t}{/a} |
		{a_remote action=destroy id=$creature _method=post _confirm=$confirm_message}{t}destroy{/t}{/a_remote}
	</td>
</tr>

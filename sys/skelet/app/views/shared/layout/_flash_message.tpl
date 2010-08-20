{if $flash->notice()}
	<div class="flash notice">{$flash->notice()|h}</div>
{/if}
{if $flash->error()}
	<div class="flash error">{$flash->error()|h}</div>
{/if}
{if $flash->success()}
	<div class="flash success">{$flash->success()|h}</div>
{/if}

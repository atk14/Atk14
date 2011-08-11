{*
 * Displays flash message if there is any.
 *
 * Beware! There is no html escaping,
 * thus one can places a link to somewhere withing the flash message or something.
 *}
{if $flash->notice()}
	<div class="flash notice">{$flash->notice()}</div>
{/if}
{if $flash->error()}
	<div class="flash error">{$flash->error()}</div>
{/if}
{if $flash->success()}
	<div class="flash success">{$flash->success()}</div>
{/if}

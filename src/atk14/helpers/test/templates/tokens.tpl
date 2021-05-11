tokens: {trim}
	{render partial="token"}
{/trim} | {trim}
	{render partial="token" token="INTERNAL"}
{/trim} | {trim}
	{assign var=token value=ASSIGNED}{render partial="token"}
{/trim} | {trim}
	{render partial="token" token="INTERNAL_AGAIN"}
{/trim}

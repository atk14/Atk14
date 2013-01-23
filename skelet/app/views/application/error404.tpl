<h2>{t}Error 404: Page not found{/t}</h2>

<p>
	{t escape=no uri=$request->getRequestUri()}We are deeply sorry, but the page on the URI <em>%1</em> wasn't found.{/t}<br />
	{t email="DEFAULT_EMAIL"|dump_constant}Please, write us to %1 if you think this is our mistake.{/t}
</p>

<p>{t}This is app/views/application/error404.tpl template{/t}</p>

<p>{a controller=main action=index}{t}Go to the homepage{/t}{/a}</p>

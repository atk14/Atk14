<h2>{t}Error 404: Page not found{/t}</h2>

<p>{t escape=no uri=$request->getRequestUri()|h}We are deeply sorry, but the page on the URI <em>%1</em> wasn't found.{/t}</p>

<p>{a controller=main action=index}{t}Go to the homepage{/t}{/a}</p>

<h2>{t}It's working!{/t}</h2>

<p>{t}Hi there, it's nice to see you!{/t}</p>

<p>{t}This HTTP request is handled by the MainController and the index() action.{/t}</p>

<p>{t}This is app/views/main/index.tpl template.{/t}</p>

<h3>{t}Where to go?{/t}</h3>
<ul>
	<li>{a controller=creatures}{t}visit the Creatures show{/t}{/a}</li>
	<li><a href="/non-existing-page">{t}check out 404 error page{/t}</a></li>

	{capture assign=url_en}{link_to lang=en}{/capture}
	{capture assign=url_cs}{link_to lang=cs}{/capture}
	<li>{t escape=no url_en=$url_en url_cs=$url_cs}switch the language: <a href="%1">english</a> or <a href="%2">czech</a>{/t}</li>
</ul>

<h3>{t}External links{/t}</h3>
<ul>
	<li><a href="http://www.atk14.net/">{t}ATK14 project website{/t}</a></li>
	<li><a href="http://book.atk14.net/">{t}The ATK14 Book{/t}</a></li>
	<li><a href="http://api.atk14.net/">{t}The API reference{/t}</a></li>
	<li><a href="https://github.com/yarri/Atk14">{t}ATK14 project page on Github{/t}</a></li>
</ul>

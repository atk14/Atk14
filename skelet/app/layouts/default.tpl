<!DOCTYPE html>
<html lang="{$lang}">

	<head>
		<meta charset="utf-8">

		<title>{$page_title|h} | {"ATK14_APPLICATION_NAME"|dump_constant}</title>
		<meta name="description" content="{$page_description|h}" />
		{render partial=shared/layout/dev_info}

		<meta name="viewport" content="width=device-width,initial-scale=1">

		{stylesheet_link_tag file="lib/blueprint-css/blueprint/screen.css" media="screen, projection"}
		{stylesheet_link_tag file="lib/blueprint-css/blueprint/print.css" media="print"}
		<!--[if IE]>
			{stylesheet_link_tag file="lib/blueprint-css/blueprint/ie.css" media="screen, projection"}
		<![endif]-->
		{stylesheet_link_tag file="styles.css" media="screen, projection"}
	</head>

	<body id="body_{$controller}_{$action}">

		<div class="container">
			<header>
				{if $controller=="main" && $action=="index"}
					<h1>{"ATK14_APPLICATION_NAME"|dump_constant}</h1>
				{else}
					<h1>{a controller=main action=index}{"ATK14_APPLICATION_NAME"|dump_constant}{/a}</h1>
				{/if}
			</header>

			<div class="main" role="main">
				{render partial=shared/layout/flash_message}
				{placeholder}
			</div>

			<footer>
			</footer>
		</div>

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="{$root|h}public/javascripts/libs/jquery/jquery-1.6.2.min.js"><\/script>')</script>
		{javascript_script_tag file="atk14.js"}
		{javascript_script_tag file="application.js"}

		<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
			chromium.org/developers/how-tos/chrome-frame-getting-started -->
		<!--[if lt IE 7 ]>
			<script defer src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
			<script defer>window.attachEvent('onload',function()\{CFInstall.check(\{mode:'overlay'\})\})</script>
		<![endif]-->
	</body>
</html>

{*
 * The page Layout template
 * 
 * Placeholders
 * ------------
 *	head						 	located whithin the <head> tag
 *	main							the main (or default) one
 *	js_script_tags		place for javascript script tags
 *	js								place for javascript code
 *	domready					place for domready javascript code
 *}
<!DOCTYPE html>
<html lang="{$lang}">

	<head>
		<meta charset="utf-8">

		<title>{$page_title} | {"ATK14_APPLICATION_NAME"|dump_constant}</title>
		<meta name="description" content="{$page_description}" />
		{render partial="shared/layout/dev_info"}

		<meta name="viewport" content="width=device-width,initial-scale=1">

		{stylesheet_link_tag file="lib/blueprint-css/blueprint/screen.css" media="screen, projection"}
		{stylesheet_link_tag file="lib/blueprint-css/blueprint/print.css" media="print"}
		<!--[if IE]>
			{stylesheet_link_tag file="lib/blueprint-css/blueprint/ie.css" media="screen, projection"}
		<![endif]-->
		{stylesheet_link_tag file="styles.css" media="screen, projection"}
		{placeholder for=head}
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
				{render partial="shared/layout/flash_message"}
				{placeholder}
			</div>

			<footer>
				<hr />
				{t escape=no}This site runs on <a href="http://www.atk14.net/">ATK14 Framework</a>, for now and ever after{/t}
			</footer>
		</div>



		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="{$public}javascripts/libs/jquery/jquery-1.7.1.min.js"><\/script>')</script>
		{javascript_script_tag file="atk14.js"}
		{javascript_script_tag file="application.js"}
		{placeholder for=js_script_tags}
		{javascript_tag}
			{placeholder for=js}
			$(function() \{
				{placeholder for=domready}
			\});
		{/javascript_tag}

		<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
			chromium.org/developers/how-tos/chrome-frame-getting-started -->
		<!--[if lt IE 7 ]>
			<script defer src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
			<script defer>window.attachEvent('onload',function()\{CFInstall.check(\{mode:'overlay'\})\})</script>
		<![endif]-->
	</body>
</html>

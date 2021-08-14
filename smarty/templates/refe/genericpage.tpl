{config_load file='templates.conf'}
<!DOCTYPE html>
<html lang="en">

<head>
	<title>{block name="title" nocache}Welcome{/block}</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="shortcut icon" href="{$basePath}/favicon.ico" type="image/x-icon">
	<link rel="icon" href="{$basePath}/favicon.ico" type="image/x-icon">
{include file='bootstrap_head.tpl'}
{include file='fontawesome_head.tpl'}
{block name='head'}{/block}
</head>

<body>
{block name='body'}{/block}
{include file='footer.tpl'}
{include file='bootstrap_scripts.tpl'}
{include file='fontawesome_scripts.tpl'}
</body>
</html>

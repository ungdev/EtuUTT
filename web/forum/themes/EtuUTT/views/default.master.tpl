<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-ca">
<head>
  {asset name='Head'}
	<link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css" type="text/css" />
</head>
<body id="{$BodyID}" class="{$BodyClass}">
  <div id="Frame">
	 <div class="Banner">
		<ul>
			<li><a href="/forum/index.php?p=/">Accueil</a></li>
		  {dashboard_link}
		  {discussions_link}
		  {activity_link}
		  {custom_menu}
		</ul>
	 </div>
	 <div id="Body">
		<div class="Wrapper">
		  <div id="Content">
			 {asset name="Content"}
		  </div>
		</div>
	 </div>
	 <div id="Foot">
		<div><a href="{vanillaurl}"><span>Powered by Vanilla</span></a></div>
		{asset name="Foot"}
	 </div>
  </div>
</body>
</html>
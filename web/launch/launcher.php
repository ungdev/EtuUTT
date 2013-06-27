<?php

include 'config.php';

if ($now < $launch) {
	header('Location: launch');
	exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">

		<title>EtuUTT est arrivé !</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="EtuUTT est arrivé !">
		<meta name="keywords" content="etuutt, arrived, finished">
		<meta name="author" content="Titouan Galopin">

		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.min.css">
		<link rel="stylesheet" type="text/css" href="etuutt/css/global.css">
		<link rel="stylesheet" type="text/css" href="etuutt/css/launcher.css">

		<script src="etuutt/js/modernizr.custom.js"></script>
	</head>
	<body>
		<div class="text center" id="1">
			Vous l'avez attendu ...
		</div>

		<div class="text center" id="2">
			Vous l'avez espéré ...
		</div>

		<div class="text center" id="3">
			Vous l'avez imaginé ...
		</div>

		<div class="text center" id="4">
			Il est arrivé
		</div>

		<div class="text center" id="5">
			<h1 class="title">EtuUTT</h1>
			<p class="desc">
				Le nouveau site étudiant de l'UTT ouvre ses portes
			</p>
			<p>
				<a href="/" class="btn btn-large btn-primary">Découvrez-le</a>
			</p>
		</div>

		<script src="etuutt/js/jquery-1.8.3.min.js"></script>
		<script src="etuutt/js/launcher.js"></script>
	</body>
</html>
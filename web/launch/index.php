<?php

include 'config.php';

$now = new DateTime();

if ($now >= $launch) {
	header('Location: launcher');
	exit;
}

$interval = $launch->diff($now);
$acceptTesters = $launch->diff($now)->days > 50;
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">

		<title>EtuUTT arrive bientôt ...</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="EtuUTT arrive bientôt ...">
		<meta name="keywords" content="etuutt, comming, soon, working, loading">
		<meta name="author" content="Titouan Galopin">

		<link rel="stylesheet" type="text/css" href="/launch/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="/launch/bootstrap/css/bootstrap-responsive.min.css">
		<link rel="stylesheet" type="text/css" href="/launch/etuutt/css/launch.css">

		<script src="/launch/etuutt/js/modernizr.custom.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="row-fluid">
				<div class="span12">
					<h1>EtuUTT</h1>
				</div>
			</div>

			<div class="row-fluid">
				<p>
					Le site étudiant de l'Université de Technologie de Troyes est en évolution ...
				</p>
			</div>

			<div class="row-fluid">
				<div class="span3 days">
					<h2 id="days"><?php echo $interval->days; ?></h2>
					<span class="infos">jour<span id="s_days">s</span></span>
				</div>
				<div class="span3 hours">
					<h2 id="hours"><?php echo $interval->h; ?></h2>
					<span class="infos">heure<span id="s_hours">s</span></span>
				</div>
				<div class="span3 minutes">
					<h2 id="minutes"><?php echo $interval->i; ?></h2>
					<span class="infos">minute<span id="s_minutes">s</span></span>
				</div>
				<div class="span3 seconds">
					<h2 id="seconds"><?php echo $interval->s; ?></h2>
					<span class="infos">seconde<span id="s_seconds">s</span></span>
				</div>
			</div>

			<div class="row-fluid row-social">
				<div class="span12 testing">
					<a href="<?php echo $facebookUrl; ?>" target="_blank" class="sharer" id="facebookShare"></a>
					<a href="<?php echo $twitterUrl; ?>" target="_blank" class="sharer" id="twitterShare"></a>
					<a href="<?php echo $mailto; ?>" class="sharer" id="mailShare"></a>
				</div>
			</div>

			<?php if ($acceptTesters) : ?>
			<div class="row-fluid hidden-phone">
				<div class="span3"></div>
				<div class="span6 testing">
					<p>
						Vous voulez aider ? Faites parti des bêta-testeurs :
					</p>

					<form>
						<div class="input-append">
							<input class="span5" placeholder="Email" type="text">
							<button class="btn" type="button">S'inscrire</button>
						</div>
					</form>
				</div>
				<div class="span3"></div>
			</div>

			<div class="row-fluid visible-phone">
				<div class="span12 testing">
					<p>
						Vous voulez aider ? Faites parti des bêta-testeurs :
					</p>

					<form>
						<div class="input-append">
							<input class="span5" placeholder="Email" type="text">
							<button class="btn" type="button">S'inscrire</button>
						</div>
					</form>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<script type="text/javascript" src="/launch/etuutt/js/jquery-1.8.3.min.js"></script>
		<script type="text/javascript">
			var interval = <?php echo json_encode($interval); ?>;

			function refreshDisplay() {
				interval.s = interval.s - 1;

				if (interval.s == -1) {
					interval.s = 59;
					interval.i = interval.i - 1;
				}

				if (interval.i == -1) {
					interval.i = 59;
					interval.h = interval.h - 1;
				}

				if (interval.h == -1) {
					interval.h = 23;
					interval.days = interval.days - 1;
				}

				if (interval.days == -1) {
					location.assign(location.href);
				} else {
					$('#days').text(interval.days);
					$('#hours').text(interval.h);
					$('#minutes').text(interval.i);
					$('#seconds').text(interval.s);

					if (interval.days <= 1) {
						$('#s_days').hide();
					} else {
						$('#s_days').show();
					}

					if (interval.h <= 1) {
						$('#s_hours').hide();
					} else {
						$('#s_hours').show();
					}

					if (interval.i <= 1) {
						$('#s_minutes').hide();
					} else {
						$('#s_minutes').show();
					}

					if (interval.s <= 1) {
						$('#s_seconds').hide();
					} else {
						$('#s_seconds').show();
					}
				}
			}

			refreshDisplay();

			setInterval(refreshDisplay, 1000);
		</script>
	</body>
</html>
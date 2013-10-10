<?php

include 'config.php';

if ($now >= $launch) {
	header('Location: ../');
	exit;
}

$diff = $launch - $now;

$interval = new stdClass();

$interval->days = floor($diff / (24 * 3600));
$days = $interval->days * 24 * 3600;

$interval->h = floor(($diff - $days) / 3600);
$hours = $interval->h * 3600;

$interval->i = floor(($diff - $days - $hours) / 60);
$minutes = $interval->i * 60;

$interval->s = $diff - $days - $hours - $minutes;
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

		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.min.css">
		<link rel="stylesheet" type="text/css" href="etuutt/css/global.css">
		<link rel="stylesheet" type="text/css" href="etuutt/css/launch.css">
		<link rel="stylesheet" type="text/css" href="facebox/src/facebox.css">

		<script src="etuutt/js/modernizr.custom.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="row-fluid">
				<div class="span12">
					<h1>EtuUTT</h1>
				</div>
			</div>

			<br />
			<br />
			<br />

			<div class="row-fluid">
				<div class="span3 days date-element">
					<h2 id="days"><?php echo $interval->days; ?></h2>
					<span class="infos">jour<span id="s_days">s</span></span>
				</div>
				<div class="span3 hours date-element">
					<h2 id="hours"><?php echo $interval->h; ?></h2>
					<span class="infos">heure<span id="s_hours">s</span></span>
				</div>
				<div class="span3 minutes date-element">
					<h2 id="minutes"><?php echo $interval->i; ?></h2>
					<span class="infos">minute<span id="s_minutes">s</span></span>
				</div>
				<div class="span3 seconds date-element">
					<h2 id="seconds"><?php echo $interval->s; ?></h2>
					<span class="infos">seconde<span id="s_seconds">s</span></span>
				</div>
			</div>

			<br />
			<br />
			<br />

			<br />
			<br />
			<br />

			<br />
			<br />
			<br />

			<div class="row-fluid">
				<div class="span12">
					<p>
						<a href="http://www-etu.utt.fr" class="btn btn-primary">
							Aller sur l'ancien site étu
						</a>
					</p>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="etuutt/js/jquery-1.8.3.min.js"></script>
		<script type="text/javascript" src="etuutt/js/launch.js"></script>
		<script type="text/javascript" src="facebox/src/facebox.js"></script>

		<script type="text/javascript">
			var interval = <?php echo json_encode($interval); ?>;
			refreshDisplay(interval);
			setInterval(function() { refreshDisplay(interval); }, 1000);

			var subscribeTestingBtn = $('#subscribe-testing-btn'),
				subscribeTestingEmail = $('#subscribe-testing-email'),
				subscribeTestingMessage = $('#subscribe-testing-message'),
				email;

			var sendAjax = function() {
				email = subscribeTestingEmail.val();
				subscribeTestingMessage.hide();

				subscribeTestingEmail.addClass('testing-loading').attr('disabled', 'disabled');
				subscribeTestingBtn.attr('disabled', 'disabled');

				$.getJSON('/launch/subscribe.php', { email: email }, function(data) {
					subscribeTestingEmail.removeClass('testing-loading').removeAttr('disabled');
					subscribeTestingBtn.removeAttr('disabled');

					if (data.status == 'success') {
						subscribeTestingMessage.removeClass('text-error').addClass('text-success');
					} else {
						subscribeTestingMessage.removeClass('text-success').addClass('text-error');
					}

					subscribeTestingMessage.text(data.content).show();
				});
			};

			subscribeTestingEmail.keypress(function(event) {
				if (event.which == 13) {
					event.preventDefault();
					sendAjax();
				}
			});

			subscribeTestingBtn.click(sendAjax);

			$('a[rel*=facebox]').facebox();
		</script>
	</body>
</html>
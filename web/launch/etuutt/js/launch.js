
var $days = $('#days'), $hours = $('#hours'), $minutes = $('#minutes'), $seconds = $('#seconds');

function launchNow() {
	$days.text(0);
	$hours.text(0);
	$minutes.text(0);
	$seconds.text(0);

	$('.date').show();
	$('.row-testing').hide();

	location.assign(location.href);
}

function refreshDisplay(diff) {
	diff = Math.abs(diff);
	diff = (diff - (diff % 1000)) / 1000;
	diff = (diff - (seconds = diff % 60)) / 60;
	diff = (diff - (minutes = diff % 60)) / 60;
	days = (diff - (hours = diff % 24)) / 24;

	$days.text(days);
	$hours.text(hours);
	$minutes.text(minutes);
	$seconds.text(seconds);

	if (days <= 1) {
		$('#s_days').hide();
	} else {
		$('#s_days').show();
	}

	if (hours <= 1) {
		$('#s_hours').hide();
	} else {
		$('#s_hours').show();
	}

	if (minutes <= 1) {
		$('#s_minutes').hide();
	} else {
		$('#s_minutes').show();
	}

	if (seconds <= 1) {
		$('#s_seconds').hide();
	} else {
		$('#s_seconds').show();
	}
}

var launch = new Date(launchYear, launchMonth, launchDay, launchHour, launchMinute, launchSecond);
var now = new Date();

var diff = now - launch;
var sign = diff < 0 ? -1 : 1;
var seconds, minutes, hours, days;

if (sign == 1) {
	launchNow();
} else {
	refreshDisplay(diff);
	$('.date').show();

	setInterval(function() {
		now = new Date();
		diff = now - launch;
		sign = diff < 0 ? -1 : 1;

		if (sign == 1) {
			launchNow();
		} else {
			refreshDisplay(diff);
		}
	}, 1000);
}
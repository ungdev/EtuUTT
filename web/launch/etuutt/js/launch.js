
function refreshDisplay(interval) {
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

function checkEmailValidity(email) {
	var regex = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

	return regex.test(email);
}

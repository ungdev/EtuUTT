var calendar = $('#calendar'),
	loader = $('#loader');

$(function() {
	var options = {
		firstDay: 1,
		monthNames: monthNames,
		monthNamesShort: monthNamesShort,
		dayNames: dayNames,
		dayNamesShort: dayNamesShort,
		buttonText: {
			today: 'Aujourd\'hui',
			month: 'Mois',
			week: 'Semaine',
			day: 'Jour'
		},

		// time formats
		allDaySlot: true,
		allDayText: 'Journée complète',
		firstHour: 6,
		slotMinutes: 30,
		defaultEventMinutes: 120,
		axisFormat: 'HH:mm',
		timeFormat: {
			agenda: 'HH:mm{ - HH:mm}',
			'': 'HH:mm'
		},
		minTime: 0,
		maxTime: 24,

		events: source,

		loading: function(isLoading) {
			if (isLoading) {
				calendar.css({ opacity: 0.7 });
			} else {
				calendar.css({ opacity: 1 });
			}
		}
	};

	if ($(window).width() > 514) {
		options.defaultView = 'month';

		options.header = {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		};
	} else {
		options.defaultView = 'monthListView';

		options.header = {
			left: 'title',
			center: '',
			right: 'prev,next'
		};
	}

	calendar.fullCalendar(options);
});

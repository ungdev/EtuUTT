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

	if ($(window).width() > 680) {
		options.defaultView = 'month';

		options.header = {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		};
	} else {
		options.defaultView = 'monthList';

		options.header = {
			left: 'title',
			center: '',
			right: 'prev,next'
		};
	}

	calendar.fullCalendar(options);
});




var FC = $.fullCalendar;
var MonthView = FC.views.month;

var MonthListView = MonthView.extend({
	renderHtml: function() {
		return '' +
			'<div class="fc-day-grid-container">' +
				'<div class="fc-month-list"></div>' +
			'</div>';
	},

	renderEvents: function(events) {
		var container = $('.fc-month-list');
		container.html('');

		if (events.length > 0) {
			var list = $('<ul>');

			var event, item;

			for (var i in events) {
				event = events[i];

				item = $('<li><div class="date"><span class="day" /></div><h5><a /></h5></li>');

				item.find('.day').text(event._start.format('DD/MM'));
				item.find('a').attr('href', event.url).text(event.title);

				list.append(item);
			}

			container.append(list);
		} else {
			var none = $('<p class="fc-month-list-none">Aucun événement prévu pour le moment</p>');

			container.append(none);
		}
	}
});

FC.views.monthList = MonthListView;

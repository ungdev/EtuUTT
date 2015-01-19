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
			prev: "<span class='fc-text-arrow'>&lsaquo;</span>",
			next: "<span class='fc-text-arrow'>&rsaquo;</span>",
			prevYear: "<span class='fc-text-arrow'>&laquo;</span>",
			nextYear: "<span class='fc-text-arrow'>&raquo;</span>",
			today: 'Aujourd\'hui',
			month: 'Mois',
			week: 'Semaine',
			day: 'Jour'
		},

		// time formats
		titleFormat: {
			month: 'MMMM yyyy',
			week: "dd MMM yyyy",
			day: 'dddd dd MMM yyyy'
		},
		columnFormat: {
			month: 'ddd',
			week: 'ddd dd/MM',
			day: 'dddd dd MMMM'
		},
		allDaySlot: true,
		allDayText: 'Journée<br />complète',
		firstHour: 6,
		slotMinutes: 30,
		defaultEventMinutes: 120,
		axisFormat: 'HH:mm',
		timeFormat: {
			agenda: 'HH:mm{ - HH:mm}',
			'': 'HH:mm'
		},
		dragOpacity: {
			agenda: .5
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


$.fullCalendar.views.monthListView = MonthListView;

function MonthListView(element, calendar) {
	var t = this;


	// exports
	t.render = render;


	// imports
	MonthListView.call(t, element, calendar, 'basicWeek');
	var opt = t.opt;
	var renderBasic = t.renderBasic;
	var skipHiddenDays = t.skipHiddenDays;
	var getCellsPerWeek = t.getCellsPerWeek;
	var formatDates = calendar.formatDates;


	function render(date, delta) {

		if (delta) {
			addDays(date, delta * 7);
		}

		var start = addDays(cloneDate(date), -((date.getDay() - opt('firstDay') + 7) % 7));
		var end = addDays(cloneDate(start), 7);

		var visStart = cloneDate(start);
		skipHiddenDays(visStart);

		var visEnd = cloneDate(end);
		skipHiddenDays(visEnd, -1, true);

		var colCnt = getCellsPerWeek();

		t.start = start;
		t.end = end;
		t.visStart = visStart;
		t.visEnd = visEnd;

		t.title = formatDates(
			visStart,
			addDays(cloneDate(visEnd), -1),
			opt('titleFormat')
		);

		renderBasic(1, colCnt, false);
	}


}

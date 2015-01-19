
var calendar = $('#calendar');
var loader = $('#loader');

var calendarManager = {
	persist: function(event) {
		var data = {
			id: event.id,
			allDay: event.allDay
		};

		if (event.start) {
			data.start = moment(event.start).format('DD-MM-YYYY--HH-mm');
		}

		if (event.end) {
			data.end = moment(event.end).format('DD-MM-YYYY--HH-mm');
		}

		$.post(
			Routing.generate('memberships_orga_events_ajax_edit', { login: orgaLogin, id: event.id }),
			{ event: data },
			function(data) {
				loader.hide();
			}
		);
	}
};

$(function() {
	calendar.fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		date: currentDate.day,
		month: currentDate.month,
		year: currentDate.year,
		selectable: true,
		selectHelper: true,
		editable: true,
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

		eventDrop: function(event) {
			calendarManager.persist(event);
		},
		eventResize: function(event) {
			calendarManager.persist(event);
		},
		select: function(start, end, allDay) {
			start = moment(start).format('DD-MM-YYYY--HH-mm');
			end = moment(end).format('DD-MM-YYYY--HH-mm');

			window.location.href = createUrl+'?s='+start+'&e='+end+'&a='+allDay;
		}
	});
});

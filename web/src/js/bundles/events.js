
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
		height: 750,
		monthNames: monthNames,
		monthNamesShort: monthNamesShort,
		dayNames: dayNames,
		dayNamesShort: dayNamesShort,
		themeButtonIcons: {
			prev: 'circle-triangle-w',
			next: 'circle-triangle-e',
			prevYear: 'seek-prev',
			nextYear: 'seek-next'
		},
		buttonText: {
			today: 'Aujourd\'hui',
			month: 'Mois',
			week: 'Semaine',
			day: 'Jour'
		},
		defaultView:'agendaWeek',

		// time formats
		titleFormat: {
			month: 'MMMM YYYY',
			week: "DD MMM YYYY",
			day: 'dddd DD MMM YYYY'
		},
		columnFormat: {
			month: 'ddd',
			week: 'ddd DD/MM',
			day: 'dddd DD MMMM'
		},
		allDaySlot: false,
		scrollTime: '08:00:00',
		slotDuration: '00:30:00',
		defaultEventMinutes: 120,
		axisFormat: 'HH:mm',
		timeFormat: 'H:mm',
		dragOpacity: {
			agenda: .5
		},

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

			window.location.href = createUrl+'?s='+start+'&e='+end;
		}
	});
});

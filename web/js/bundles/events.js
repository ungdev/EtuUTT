
var subscriber = {
	button: $('#subscriber_button'),
	loader: $('#subscriber_loader'),
	current: $('#subscriber_current'),
	yes: $('#subscriber_yes'),
	probably: $('#subscriber_probably'),
	no: $('#subscriber_no')
};

var eventId = subscriber.button.attr('data-event-id');

var calendar = $('#calendar'),
	createBox = $('#create-event');

var loader = $('#loader');

subscriber.yes.click(function() {
	subscriber.current.text($(this).text());
	subscriber.button.addClass('disabled');
	subscriber.loader.show();

	$.get(Routing.generate('events_answer', {'id': eventId, 'answer': 'yes'}), function() {
		subscriber.button.removeClass('disabled');
		subscriber.loader.hide();
	});

	return false;
});

subscriber.probably.click(function() {
	subscriber.current.text($(this).text());
	subscriber.button.addClass('disabled');
	subscriber.loader.show();

	$.get(Routing.generate('events_answer', {'id': eventId, 'answer': 'probably'}), function() {
		subscriber.button.removeClass('disabled');
		subscriber.loader.hide();
	});

	return false;
});

subscriber.no.click(function() {
	subscriber.current.text($(this).text());
	subscriber.button.addClass('disabled');
	subscriber.loader.show();

	$.get(Routing.generate('events_answer', {'id': eventId, 'answer': 'no'}), function() {
		subscriber.button.removeClass('disabled');
		subscriber.loader.hide();
	});

	return false;
});


var calendarManager = {
	persist: function(event) {
		var data = {
			id: event.id,
			start: moment(event.start).format('DD-MM-YYYY--HH-mm'),
			end: moment(event.end).format('DD-MM-YYYY--HH-mm'),
			allDay: event.allDay
		};

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
		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Nomvembre','Décembre'],
		monthNamesShort: ['Jan','Fev','Mar','Avr','Mai','Juin','Jui','Aou','Sep','Oct','Nov','Dec'],
		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
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
			loader.show();
			calendarManager.persist(event);
		},
		eventResize: function(event) {
			loader.show();
			calendarManager.persist(event);
		},
		select: function(start, end, allDay) {
			loader.show();

			start = moment(start).format('DD-MM-YYYY--HH-mm');
			end = moment(end).format('DD-MM-YYYY--HH-mm');

			window.location.href = createUrl+'?s='+start+'&e='+end+'&a='+allDay;
		},
		loading: function(bool) {
			if (bool) {
				loader.show();
			} else {
				loader.hide();
			}
		}
	});
});

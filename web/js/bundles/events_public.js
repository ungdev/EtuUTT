
var calendar = $('#calendar'),
	loader = $('#loader');

$(function() {
	calendar.fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
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

		loading: function(bool) {
			if (bool) {
				loader.show();
			} else {
				loader.hide();
			}
		}
	});
});

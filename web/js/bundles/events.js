
var subscriber = {
	button: $('#subscriber_button'),
	loader: $('#subscriber_loader'),
	current: $('#subscriber_current'),
	yes: $('#subscriber_yes'),
	probably: $('#subscriber_probably'),
	no: $('#subscriber_no')
};

var eventId = subscriber.button.attr('data-event-id');

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

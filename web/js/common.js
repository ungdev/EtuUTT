
$('#change-locale-link').click(function() {
	$('#change-locale-link').toggleClass('change-locale-link');
	$('#next-change-locale-link').toggleClass('next-change-locale-link');
	$('#change-locale').toggle();
	return false;
});

$('.userbox-link').click(function() {
	$('.userbox').toggleClass('userbox-clicked');
	$('.userbox-menu').toggleClass('userbox-menu-clicked');
	$('.userbox-menu').toggle();
	return false;
});

$('#more').click(function() {
	if (! $(this).hasClass('active')) {
		$('#head-menu-list li').each(function() {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active').addClass('old-active');
			}
		});

		$(this).addClass('active');
	} else {
		$('#head-menu-list li').each(function() {
			if ($(this).hasClass('old-active')) {
				$(this).removeClass('old-active').addClass('active');
			}
		});

		$(this).removeClass('active');
	}

	$('#overlay').toggle();
	$('#overlay-content').toggle();

	return false;
});

$('body').click(function() {
	$('.userbox').removeClass('userbox-clicked');
	$('.userbox-menu').removeClass('userbox-menu-clicked');
	$('.userbox-menu').hide();
	$('#change-locale-link').removeClass('change-locale-link');
	$('#next-change-locale-link').removeClass('next-change-locale-link');
	$('#change-locale').hide();

	$('#head-menu-list li').each(function() {
		if ($(this).hasClass('old-active')) {
			$(this).removeClass('old-active').addClass('active');
		}
	});

	$(this).removeClass('active');

	$('#overlay').hide();
	$('#overlay-content').hide();
});

$('.userbox a').click(function() {
	return true;
});

$(function() {

	// Find new notifications
	$.getJSON(Routing.generate('notifs_new'), function(data) {
		if (typeof data.status != 'undefined' && data.status == 200) {
			$('#head-menu-home-pins').text(data.result);
			$('#head-menu-home-pins').show();
		}
	});


	// Load Redactor
	$('.redactor').redactor({
		fixed: true,
		lang: 'es',
		buttons: ['formatting', '|', 'bold', 'italic', 'deleted', '|',
			'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
			'image', 'video', 'file', 'table', 'link', '|',
			'fontcolor', 'backcolor', '|', 'alignment']
	});


	// Suscribe
	$('.subscription-subscribe').click(function() {
		var url = Routing.generate('notifs_subscribe', {
			'entityType': $(this).attr('data-entityType'),
			'entityId': $(this).attr('data-entityId')
		});

		var id = $(this).attr('id').replace('-subscribe', '');

		$('#'+ id +'-subscribe').hide();
		$('#'+ id +'-loader').show();

		$.getJSON(url, function(data) {
			$('#'+ id +'-loader').hide();

			if (typeof data.status != 'undefined' && data.status == 200) {
				$('#'+ id +'-subscribe').hide();
				$('#'+ id +'-unsubscribe').show();
			} else {
				$('#'+ id +'-subscribe').show();

				alert('Une erreur s\'est produite. Veuillez réessayer ou signaler le problème.');
			}
		});
	});


	// Unsuscribe
	$('.subscription-unsubscribe').click(function() {
		var url = Routing.generate('notifs_unsubscribe', {
			'entityType': $(this).attr('data-entityType'),
			'entityId': $(this).attr('data-entityId')
		});

		var id = $(this).attr('id').replace('-unsubscribe', '');

		$('#'+ id +'-unsubscribe').hide();
		$('#'+ id +'-loader').show();

		$.getJSON(url, function(data) {
			$('#'+ id +'-loader').hide();

			if (typeof data.status != 'undefined' && data.status == 200) {
				$('#'+ id +'-unsubscribe').hide();
				$('#'+ id +'-subscribe').show();
			} else {
				$('#'+ id +'-unsubscribe').show();

				alert('Une erreur s\'est produite. Veuillez réessayer ou signaler le problème.');
			}
		});
	});
});
